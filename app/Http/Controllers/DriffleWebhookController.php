<?php

namespace App\Http\Controllers;

use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DriffleWebhookController extends Controller
{
    protected SupplierService $supplier;

    public function __construct(SupplierService $supplier)
    {
        $this->supplier = $supplier;
    }

    public function reservation(Request $request)
    {
        $orderData = [
            "products" => $request->input('products') ?? 
        ];

        $supplierResponse = $this->supplier->createOrder($orderData);

        return response()->json([
            "success" => true,
            "data" => $supplierResponse
        ]);
    }

    public function provision(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Provision processed",
            "data" => $request->all()
        ]);
    }

    public function cancellation(Request $request)
    {
        return response()->json([
            "success" => true,
            "message" => "Cancellation processed",
            "data" => $request->all()
        ]);
    }
}
