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
}
