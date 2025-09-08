<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DriffleService;

class FetchDriffleProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:driffle-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch products from Driffle and store them in a variable';

    protected DriffleService $driffleService;

    public function __construct(DriffleService $driffleService)
    {
        parent::__construct();
        $this->driffleService = $driffleService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = $this->driffleService->getProducts(); // defaults: page=1, limit=100
        dd($products);

        // Optional feedback (doesn't output product data)
        $this->info('Fetched ' . (is_countable($products) ? count($products) : 0) . ' products from Driffle.');

        // You can now use $products as needed within this command
        // e.g., process or persist them.
    }
}
