<?php

namespace App\Services;

use App\Models\SupplierProduct;
use Illuminate\Support\Facades\Http;

class SupplierService
{
    protected string $supplierUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->supplierUrl = config('credentials.supplier.base_url');
        $this->apiKey = config('credentials.supplier.api_key');
    }

    public function fetch()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey
            ])->get($this->supplierUrl);

            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch supplier products: '.$e->getMessage());
        }
    }

    public function insert()
    {
        try {
        $response = collect($this->fetch());
        foreach ($response as $product) {
            SupplierProduct::create([
                'uuid' => $product['_id'],
                'internal_id' => $product['internalId'],
                'product_data' => json_encode($product),
            ]);
        }
            return $response;
        } catch (\Exception $e) {
            throw new \Exception('Failed to insert supplier products: '.$e->getMessage());
        }
    }

    public function createOrder(array $orderData)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey
            ])->post($this->supplierUrl.'/order', $orderData);

            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception('Failed to create supplier order: '.$e->getMessage());
        }
    }

}
