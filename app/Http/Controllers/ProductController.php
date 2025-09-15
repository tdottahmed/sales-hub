<?php

namespace App\Http\Controllers;

use App\Models\DriffleOffer;
use App\Models\DriffleProduct;
use App\Models\Product;
use App\Models\SimilarProduct;
use App\Services\DriffleService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('admin.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function driffleManualMap()
    {
        return view('admin.offers.driffle-manual-map');
    }

    public function driffleCreateOffer()
    {
        $products = SimilarProduct::with(['driffleProduct', 'product'])->where('source', 'driffle')->paginate(20);
        return view('admin.offers.mapped', compact('products'));
    }
    public function offerList()
    {
        $offers = DriffleOffer::with('driffleProduct')->paginate(20);
        return view('admin.offers.index', compact('offers'));
    }

    public function driffleProducts(Request $request)
    {
        $query = DriffleProduct::select('id', 'product_id', 'title', 'platform', 'regions');
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('regions', 'like', "%{$search}%");
            });
        }
        $products = $query->paginate(20)->withQueryString();
        return view('admin.products.driffle-products', compact('products'));
    }

    public function driffleProductsShow(DriffleProduct $driffleProduct)
    {
        dd($driffleProduct);
    }

    public function driffleMapProducts()
    {
        $products = SimilarProduct::with(['driffleProduct', 'product'])->where('source', 'driffle')->paginate(20);
        return view('admin.products.mapped-driffle', compact('products'));
    }

    public function createOffer(SimilarProduct $similarProduct)
    {

        // get price from product variation
        $price = $similarProduct->product->min_price;

        // Call DriffleService to create offer
        $driffleService = new DriffleService();
        $response = $driffleService->createOffer($similarProduct->driffle_product_id, $price);

        try {
            // if response is success then create driffle offer
            if (isset($response) && $response['statusCode'] === 1) {
                DriffleOffer::create([
                    'offer_id' => $response['offer']['offerId'] ?? null,
                    'driffle_product_id' => $response['offer']['productId'] ?? null,
                    'your_price' => $response['offer']['yourPrice'] ?? null,
                    'retail_price' => $response['offer']['yourPrice'] ?? null,
                    'final_selling_price' => $response['offer']['finalSellingPrice'] ?? null,
                    'available_stock' => $response['offer']['availableStock'] ?? null,
                    'product_variation_id' => $similarProduct->product_variation_id,
                ]);
            }

            return redirect()->back()->with('success', 'Offer created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating offer: ' . $e->getMessage());
        }
    }

    public function updateOffer(DriffleOffer $offer)
    {
        try {
            // Call DriffleService to update offer
            $driffleService = new DriffleService();
            $response = $driffleService->updateOffer(
                $offer->offer_id,
                $offer->your_price,
                // $offer->retail_price,
            );

            // Check if response is successful
            if (isset($response) && $response['statusCode'] === 1) {
                // Update the offer in database if needed
                $offer->update([
                    'your_price' => $response['data']['yourPrice'] ?? $offer->your_price,
                    // 'retail_price' => $response['data']['retailPrice'] ?? $offer->retail_price,
                    'final_selling_price' => $response['data']['finalSellingPrice'] ?? $offer->final_selling_price,
                    'available_stock' => $response['data']['availableStock'] ?? $offer->available_stock,
                ]);

                return redirect()->back()->with('success', 'Offer updated successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to update offer: ' . ($response['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating offer: ' . $e->getMessage());
        }
    }

    public function updateOfferPrice(DriffleOffer $offer)
    {
        try {
            // Call DriffleService to update offer price
            $driffleService = new DriffleService();
            $response = $driffleService->updateOfferPrice(
                $offer->offer_id,
                $offer->your_price
            );

            // Check if response is successful
            if (isset($response) && $response['statusCode'] === 200) {
                // Update the offer price in database if needed
                $offer->update([
                    'your_price' => $response['data']['yourPrice'] ?? $offer->your_price,
                    'final_selling_price' => $response['data']['finalSellingPrice'] ?? $offer->final_selling_price,
                ]);

                return redirect()->back()->with('success', 'Offer price updated successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to update offer price: ' . ($response['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating offer price: ' . $e->getMessage());
        }
    }

    public function toggleOffer(DriffleOffer $offer)
    {
        try {
            // Determine the new status based on current status
            $newStatus = ($offer->status === 'disable') ? 'enable' : 'disable';
            
            // Call DriffleService to toggle offer (enable/disable)
            $driffleService = new DriffleService();
            $response = $driffleService->toggleOffer($offer->offer_id, $newStatus);

            // Check if response is successful
            if (isset($response) && $response['statusCode'] === 1) {
                // Update the status in the database
                $offer->update(['status' => $newStatus]);
                
                return redirect()->back()->with('success', "Offer {$newStatus}d successfully");
            } else {
                return redirect()->back()->with('error', 'Failed to toggle offer: ' . ($response['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error toggling offer: ' . $e->getMessage());
        }
    }
}
