<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSupplierProductJob;
use App\Models\Product;
use App\Models\SupplierProduct;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function index()
    {
        $products = SupplierProduct::where('is_processed', false)->take(1)->get();
        foreach ($products as $product) {
            Product::updateOrCreate(
                ['internal_id' => $this->$product->internal_id],
                [
                    'name' => $data['name'] ?? 'Unknown',
                    'description' => $data['description'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'currency_code' => $data['currencyCode'] ?? null,
                    'disclaimer' => $data['disclaimer'] ?? null,
                    'redemption_instructions' => $data['redemptionInstructions'] ?? null,
                    'logo_url' => $data['logoUrl'] ?? null,
                    'modified_date' => $data['modifiedDate'] ?? null,
                ]
            );
        }
    }
}
