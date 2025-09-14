<?php

namespace App\Http\Controllers;

use App\Models\DriffleProduct;
use App\Models\Product;
use App\Models\SimilarProduct;
use App\Models\SupplierProduct;
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
        $products = SimilarProduct::with(['driffleProduct', 'product'])->paginate(20);
        return view('admin.products.mapped-driffle', compact('products'));
    }

    public function createOffer(SimilarProduct $similarProduct)
    {
        dd($similarProduct);
        return view('admin.products.create-offer');
    }
}
