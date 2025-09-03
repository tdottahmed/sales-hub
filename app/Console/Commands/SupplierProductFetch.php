<?php

namespace App\Console\Commands;

use App\Services\FetchSupplierProduct;
use Illuminate\Console\Command;

class SupplierProductFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:supplier-products {--force : Force fetch products without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch supplier products from external API';

    /**
     * Execute the console command.
     */
    public function handle(FetchSupplierProduct $fetchSupplierProduct)
    {
        try {
            if (!$this->option('force') && !$this->confirm('Do you wish to fetch supplier products?')) {
                $this->info('Command cancelled.');
                return;
            }

            $this->info('Fetching supplier products...');
            $products = $fetchSupplierProduct->fetch();

            $this->info('Inserting products...');
            $fetchSupplierProduct->insert();

            $this->info('Supplier products fetched and inserted successfully.');
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            return 1;
        }
    }
}
