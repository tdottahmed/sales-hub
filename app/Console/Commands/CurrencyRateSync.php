<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyRateSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:currency {--force : Force sync without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize currency exchange rates from Fixer API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            if (!$this->option('force') && !$this->confirm('Do you want to sync currency rates?')) {
                $this->info('Command cancelled.');
                return Command::SUCCESS;
            }

            $apiKey = config('credentials.fixer.api_key');

            if (empty($apiKey)) {
                throw new \RuntimeException('Fixer API key not configured');
            }

            $url = "http://data.fixer.io/api/latest?access_key={$apiKey}&base=EUR";

            $response = Http::timeout(30)->get($url);

            if ($response->failed()) {
                throw new \RuntimeException("Failed request: ".$response->body());
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                throw new \RuntimeException("API Error: ".json_encode($data['error'] ?? []));
            }

            $rates = $data['rates'] ?? [];
            $updated = 0;

            foreach ($rates as $code => $rate) {
                if ($rate > 0) {
                    Currency::updateOrCreate(
                        ['code' => $code],
                        [
                            'rate_to_eur' => 1 / $rate,
                            'rate_to_usd' => ($rates['USD'] ?? 1) / $rate,
                            'name' => strtoupper($code)
                        ]
                    );
                    $updated++;
                }
            }

            $this->info("{$updated} currencies updated successfully.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error('Currency sync failed: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}
