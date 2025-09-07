<?php

namespace App\Jobs;

use App\Models\ProductVariation;
use App\Models\SupplierProduct;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CurrencyConversionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSupplierProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public $backoff = [60, 300, 900];
    public int $timeout = 120;

    protected SupplierProduct $supplierProduct;

    public function __construct(SupplierProduct $supplierProduct)
    {
        $this->supplierProduct = $supplierProduct;
    }

    public function handle(CurrencyConversionService $currencyService): void
    {
        Log::withContext([
            'job' => 'SyncSupplierProductJob',
            'supplier_product_id' => $this->supplierProduct->id ?? null,
            'supplier_internal_id' => $this->supplierProduct->internal_id ?? null,
        ]);

        Log::info('Job started');

        try {
            // Parse JSON safely
            $raw = $this->supplierProduct->product_data;
            if (empty($raw)) {
                Log::warning("Empty product_data for SupplierProduct ID: {$this->supplierProduct->id}");
                return;
            }

            $data = json_decode($raw, true);
            if (!is_array($data)) {
                Log::warning("Invalid product_data JSON for SupplierProduct ID: {$this->supplierProduct->id}");
                return;
            }

            // Create/Update Product
            $basePriceEur = $this->computeBasePriceFromVariations($data, $currencyService);
            Log::info('modified Date: ' . $data['modifiedDate']);
            $product = Product::updateOrCreate(
                ['internal_id' => $this->supplierProduct->internal_id],
                [
                    'name' => $data['name'] ?? 'Unknown',
                    'description' => $data['description'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'currency_code' => $data['currencyCode'] ?? null,
                    'disclaimer' => $data['disclaimer'] ?? null,
                    'terms' => $data['terms'] ?? null,
                    'redemption_instructions' => $data['redemptionInstructions'] ?? null,
                    'logo_url' => $data['logoUrl'] ?? null,
                    'modified_date' => isset($data['modifiedDate']) ? date('Y-m-d H:i:s',
                        strtotime($data['modifiedDate'])) : null,
                ]
            );

            // Replace the category sync code with this:
            $categoryIds = [];
            $categories = $data['categories'] ?? [];
            if (is_array($categories)) {
                foreach ($categories as $cat) {
                    $catName = $cat['name'] ?? null;
                    if (!$catName) {
                        continue;
                    }
                    // First try to find the category
                    $category = Category::where('name', $catName)->first();
                    // If not found, create it with explicit assignment
                    if (!$category) {
                        $category = new Category();
                        $category->name = $catName;
                        $category->save();
                    }
                    $categoryIds[] = $category->id;
                }
            }
            // Upsert variations from supplier `products`
            $incomingSkus = [];
            $variations = $data['products'] ?? [];
            if (is_array($variations)) {
                foreach ($variations as $var) {
                    // Use supplier product id as SKU for idempotency
                    $sku = isset($var['id']) ? (string) $var['id'] : null;
                    if (!$sku) {
                        Log::warning("Missing variation id (SKU) for SupplierProduct ID: {$this->supplierProduct->id}");
                        continue;
                    }

                    $incomingSkus[] = $sku;

                    $varName = $var['name'] ?? "Variant {$sku}";
                    $priceEur = $this->resolveVariationPriceToEur($var, $data['currencyCode'] ?? null,
                        $currencyService);

                    ProductVariation::updateOrCreate(
                        ['product_id' => $product->id, 'sku' => $sku],
                        [
                            'name' => $varName,
                            'price' => $priceEur,
                        ]
                    );
                }
            }

            // Mark SupplierProduct as synced
            $this->supplierProduct->update([
                'is_synced' => true,
                'synced_at' => now(),
            ]);

            Log::info('Job finished successfully');
        } catch (Throwable $e) {
            // Ensure we see the reason in logs and fail the job properly
            Log::error('Unhandled exception in SyncSupplierProductJob', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // rethrow so failed() is triggered and the job is recorded in failed_jobs
        }
    }

    protected function computeBasePriceFromVariations(array $data, CurrencyConversionService $currencyService): ?float
    {
        $variations = $data['products'] ?? [];
        if (!is_array($variations) || empty($variations)) {
            return null;
        }

        $minPrices = [];
        foreach ($variations as $var) {
            $price = $this->resolveVariationPriceToEur($var, $data['currencyCode'] ?? null, $currencyService);
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
        // Priority: nested price.min + nested currencyCode
        $amount = null;
        $currency = null;

        if (isset($var['price']) && is_array($var['price'])) {
            $amount = $var['price']['min'] ?? $var['price']['max'] ?? null;
            $currency = $var['price']['currencyCode'] ?? null;
        }

        // Fallback to minFaceValue/maxFaceValue with top-level currencyCode
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

    public function failed(Throwable $exception): void
    {
        Log::error("SyncSupplierProductJob failed for SupplierProduct ID {$this->supplierProduct->id}: ".$exception->getMessage(),
            [
                'trace' => $exception->getTraceAsString(),
            ]);

        // Optionally mark as not synced; keeping as-is so it can be retried.
        // $this->supplierProduct->update(['is_synced' => false]);
    }
}
