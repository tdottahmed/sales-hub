<?php

return [

    'supplier' => [
        'base_url' => env('SUPPLIER_BASE_URL', 'https://Business.ozchest.com/v1/getProducts'),
        'api_key' => env('SUPPLIER_API_KEY', 'd340d352972af93411cd92ae7a7f74b8'),
    ],
    'seller' => [
        'api_key' => env('SELLER_API_KEY', ''),
    ],
    'fixer' => [
        'api_key' => env('FIXER_API_KEY', '00000000000000000000000000000000'),
        'base_url' => env('FIXER_BASE_URL', 'https://api.exchangeratesapi.io/latest'),
    ]
];
