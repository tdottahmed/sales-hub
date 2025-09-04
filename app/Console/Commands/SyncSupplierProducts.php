<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SupplierProduct;
use App\Jobs\SyncSupplierProductJob;

class SyncSupplierProducts extends Command
{
    protected $signature = 'sync:supplier-products {--chunk=100}';
    protected $description = 'Sync supplier products into structured products/variations/categories';

    public function handle()
    {
        $chunk = (int) $this->option('chunk');

        $products = SupplierProduct::where('is_processed', false)->take(1)->get();
        foreach ($products as $product) {
            SyncSupplierProductJob::dispatch($product);
        }
        $this->info('Sync jobs dispatched successfully.');
    }
}
