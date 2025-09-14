<?php

namespace App\Http\Controllers;

use App\Models\SupplierOrder;
use App\Services\SupplierService;
use Carbon\Carbon;
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
        $dummyProducts = [
            "products" => [
                ["id" => "1234567890", "quantity" => 1],
            ],
        ];

        $orderData = [
            "products" => $request->input('products', $dummyProducts['products']),
        ];

        $supplierResponse = $this->supplier->createOrder($orderData);

        foreach ($supplierResponse as $order) {
            $formattedDate = isset($order['date'])
                ? Carbon::createFromFormat('d/m/Y', $order['date'])->format('Y-m-d')
                : null;

            SupplierOrder::updateOrCreate(
                ['supplier_order_id' => $order['_id']], // unique key
                [
                    'card_id'      => $order['cardId'] ?? null,
                    'code'         => $order['code'] ?? null,
                    'pin'          => $order['pin'] ?? null,
                    'date'         => $formattedDate,
                    'status'       => $order['status'] ?? null,
                    'currency'     => $order['currency'] ?? null,
                    'crypto_value' => $order['cryptoValue'] ?? null,
                    'order_by'     => $order['orderBy'] ?? null,
                ]
            );
        }
        return response()->json([
            "success" => true,
            "data" => $supplierResponse,
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
