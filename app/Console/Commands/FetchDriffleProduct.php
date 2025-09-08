<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DriffleService;
use App\Models\DriffleProduct;
use Illuminate\Support\Str;

class FetchDriffleProduct extends Command
{
    protected $signature = 'fetch:driffle-product-page
                            {page : Starting page number}
                            {--limit=100 : Items per page}
                            {--interval=0 : Delay between API requests in milliseconds}';

    protected $description = 'Fetch Driffle products starting at a page and continue until an empty page is returned';

    protected DriffleService $driffleService;

    public function __construct(DriffleService $driffleService)
    {
        parent::__construct();
        $this->driffleService = $driffleService;
    }

    public function handle(): int
    {
        $page     = (int) $this->argument('page');
        $limit    = (int) ($this->option('limit') ?: 200);
        $interval = max(0, (int) $this->option('interval')); // milliseconds

        if ($page <= 0) {
            $this->error('Starting page must be >= 1');
            return Command::FAILURE;
        }

        $this->info("Starting Driffle fetch from page {$page} (limit {$limit}) and continuing until an empty page is returned.");

        $totalProcessed = 0;
        $inserted = 0;
        $updated  = 0;

        while (true) {
            $this->line("Requesting page {$page}...");
            $response = $this->driffleService->getProducts($page, $limit);

            // Try common payload shapes: data | items | results
            $items = [];
            if (is_array($response)) {
                $items = $response['data'] ?? $response['items'] ?? $response['results'] ?? [];
            }

            $count = is_countable($items) ? count($items) : 0;

            if ($count === 0) {
                $this->warn("No products found on page {$page}. Stopping.");
                break;
            }

            foreach ($items as $item) {
                [$wasCreated] = $this->driffleproductInsertion((array) $item);
                $wasCreated ? $inserted++ : $updated++;
            }

            $totalProcessed += $count;
            $this->info("Page {$page} processed. Count: {$count}. Total so far: {$totalProcessed} (Inserted: {$inserted}, Updated: {$updated})");

            // Next page
            $page++;

            // Optional delay between requests
            if ($interval > 0) {
                usleep($interval * 1000);
            }
        }

        $this->info("Completed. Total processed: {$totalProcessed}. Inserted: {$inserted}, Updated: {$updated}");
        return Command::SUCCESS;
    }

    private function driffleproductInsertion(array $data): array
    {
        $productId = $data['productId'] ?? null;
        if (!$productId) {
            return [false, new DriffleProduct()];
        }

        // Collect region names
        $regionNames = [];
        if (!empty($data['regions']) && is_array($data['regions'])) {
            foreach ($data['regions'] as $region) {
                if (is_array($region) && !empty($region['name'])) {
                    $regionNames[] = (string) $region['name'];
                }
            }
        }
        $regions = count($regionNames) ? implode(',', $regionNames) : (string) ($data['regionName'] ?? '');

        $model = DriffleProduct::firstOrNew(['product_id' => (string) $productId]);
        $wasCreated = !$model->exists;

        if ($wasCreated) {
            $model->uuid = (string) Str::uuid();
            $model->product_id = (string) $productId;
        }

        $model->title        = (string) ($data['title'] ?? '');
        $model->platform     = (string) ($data['platform'] ?? '');
        $model->regions      = $regions;
        $model->product_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $model->save();

        return [$wasCreated, $model];
    }
}
