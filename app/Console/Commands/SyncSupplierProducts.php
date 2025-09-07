<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SupplierProduct;
use App\Jobs\SyncSupplierProductJob;
use App\Models\ProductVariation;
use App\Models\Product;
use App\Models\Category;
use App\Services\CurrencyConversionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSupplierProducts extends Command
{
    protected $signature = 'sync:supplier-products {--chunk=100}';
    protected $description = 'Sync supplier products into structured products/variations/categories';

    public function handle()
    {
        $chunk = (int) $this->option('chunk');
        $chunk = $chunk > 0 ? $chunk : 100;

        $currencyService = app(CurrencyConversionService::class);

        $countSynced = 0;
        $countFailed = 0;

        Log::withContext([
            'command' => 'sync:supplier-products',
        ]);

        $this->info("Starting supplier products sync with chunk size: {$chunk}");

        SupplierProduct::where('is_processed', false)
            ->orderBy('id')
            ->take(10)
            ->chunkById($chunk, function ($products) use (&$countSynced, &$countFailed, $currencyService) {
                foreach ($products as $supplierProduct) {
                    Log::withContext([
                        'supplier_product_id' => $supplierProduct->id ?? null,
                        'supplier_internal_id' => $supplierProduct->internal_id ?? null,
                    ]);
                    Log::info('Processing supplier product');

//                    try {
                        $raw = $supplierProduct->product_data;
                        if (empty($raw)) {
                            Log::warning("Empty product_data; skipping");
                            continue;
                        }

                        $data = json_decode($raw, true);
                        if (!is_array($data)) {
                            Log::warning("Invalid product_data JSON; skipping");
                            continue;
                        }

                        // Create/Update Product
                        $basePriceEur = $this->computeBasePriceFromVariations($data, $currencyService);
                        Log::info('modified Date: '.($data['modifiedDate'] ?? 'N/A'));

                        $product = Product::updateOrCreate(
                            ['internal_id' => $supplierProduct->internal_id],
                            [
                                'name' => $data['name'] ?? 'Unknown',
                                'description' => $data['description'] ?? null,
                                'country_code' => $data['countryCode'] ?? null,
                                'currency_code' => $data['currencyCode'] ?? null,
                                'disclaimer' => $data['disclaimer'] ?? null,
                                'terms' => $data['terms'] ?? null,
                                'redemption_instructions' => $data['redemptionInstructions'] ?? null,
                                'logo_url' => $data['logoUrl'] ?? null,
                                'modified_date' => isset($data['modifiedDate'])
                                    ? date('Y-m-d H:i:s', strtotime($data['modifiedDate']))
                                    : null,
                            ]
                        );

                        // Categories
                        $categoryIds = [];
                        $categories = $data['categories'] ?? [];
                        if (is_array($categories)) {
                            foreach ($categories as $cat) {
                                $catName = $cat['name'] ?? null;
                                if (!$catName) {
                                    continue;
                                }
                                $category = Category::where('name', $catName)->first();
                                if (!$category) {
                                    $category = new Category();
                                    $category->name = $catName;
                                    $category->save();
                                }
                                $categoryIds[] = $category->id;
                            }
                        }
                        if (!empty($categoryIds)) {
                            $product->categories()->syncWithoutDetaching($categoryIds);
                        }

                        // Variations
                        $variations = $data['products'] ?? [];
                        if (is_array($variations)) {
                            foreach ($variations as $var) {
                                $sku = isset($var['id']) ? (string) $var['id'] : null;
                                if (!$sku) {
                                    Log::warning("Missing variation id (SKU); skipping variation");
                                    continue;
                                }

                                $varName = $var['name'] ?? "Variant {$sku}";
                                $priceEur = $this->resolveVariationPriceToEur(
                                    $var,
                                    $data['currencyCode'] ?? null,
                                    $currencyService
                                );

                                ProductVariation::updateOrCreate(
                                    ['product_id' => $product->id],
                                    [
                                        'name' => $varName,
                                        'price' => $priceEur,
                                    ]
                                );
                            }
                        }

                        // Mark SupplierProduct as synced
                        $supplierProduct->update([
                            'is_synced' => true,
                            'synced_at' => now(),
                        ]);

                        $countSynced++;
                        Log::info('Supplier product synced successfully');
//                    } catch (Throwable $e) {
//                        $countFailed++;
//                        Log::error('Unhandled exception during command sync', [
//                            'message' => $e->getMessage(),
//                            'class' => get_class($e),
//                            'code' => $e->getCode(),
//                            'file' => $e->getFile(),
//                            'line' => $e->getLine(),
//                            'trace' => $e->getTraceAsString(),
//                        ]);
//                    }
                }
            });

        $this->info("Sync completed. Success: {$countSynced}, Failed: {$countFailed}");
    }

    protected function computeBasePriceFromVariations(array $data, CurrencyConversionService $currencyService): ?float
    {
        $variations = $data['products'] ?? [];
        if (!is_array($variations) || empty($variations)) {
            return null;
        }

        $minPrices = [];
        foreach ($variations as $var) {
            $price = $this->resolveVariationPriceToEur(
                $var,
                $data['currencyCode'] ?? null,
                $currencyService
            );
            if ($price !== null) {
                $minPrices[] = $price;
            }
        }

        return empty($minPrices) ? null : min($minPrices);
    }

    protected function resolveVariationPriceToEur(
        array $var,
        ?string $fallbackCurrency,
        CurrencyConversionService $currencyService
    ): ?float {
        $amount = null;
        $currency = null;

        if (isset($var['price']) && is_array($var['price'])) {
            $amount = $var['price']['min'] ?? $var['price']['max'] ?? null;
            $currency = $var['price']['currencyCode'] ?? null;
        }

        if ($amount === null) {
            $amount = $var['minFaceValue'] ?? $var['maxFaceValue'] ?? null;
            $currency = $currency ?? $fallbackCurrency;
        }

        if ($amount === null || !$currency) {
            return null;
        }

        try {
            return $currencyService->convertToEuro((float) $amount, (string) $currency);
        } catch (Throwable $e) {
            Log::warning('Currency conversion failed', [
                'amount' => $amount,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
