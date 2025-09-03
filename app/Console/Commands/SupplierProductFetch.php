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
    protected $signature = 'fetch:supplier-products';

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
            $fetchSupplierProduct->fetch();
            $fetchSupplierProduct->insert();
            return $this->info('Supplier products fetched and inserted successfully.');
    }
}
