<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class DriffleService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) Config::get('credentials.driffle.baseurl', 'https://services.driffle.com'), '/');
        $this->apiKey  = (string) Config::get('credentials.driffle.api_key', '');
    }

    public function getAccessToken(): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Driffle apiKey is not configured. Set credentials.driffle.api_key.');
        }

        $cacheKey = 'driffle.access_token.' . md5($this->apiKey);

        return Cache::remember($cacheKey, now()->addMinutes(29), function () {
            $response = Http::baseUrl($this->baseUrl)
                ->asJson()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('/api/seller/legacy/token', ['apiKey' => $this->apiKey])
                ->throw();

            $json = $response->json() ?? [];

            return $json['token']
                ?? ($json['data']['token'] ?? null)
                ?? ($json['access_token'] ?? null)
                ?? throw new \RuntimeException('Unable to retrieve token from Driffle token endpoint.');
        });
    }

    protected function withAuth()
    {
        $token = $this->getAccessToken();

        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withToken($token)
            ->withHeaders(['Content-Type' => 'application/json']);
    }

    /**
     * Create a new offer
     */
    public function createOffer(int $productId, float $yourPrice): array
    {

        $seller_api_403_error = config('app.driffle_endpoint_403', false);

        // check if api_403_error is true then read dummy json file from public/dummy-response/offers/create.json

        $response = null;
        if ($seller_api_403_error) {
            $response = file_get_contents(public_path('dummy-response/offers/create.json'));
            return json_decode($response, true);
        }

        // if empty product id or your price return
        if (empty($productId) || empty($yourPrice)) {
            return ['error' => 'Product id or your price is empty'];
        }

        $response = $this->withAuth()->post('/api/seller/legacy/offer', [
            'product'   => ['productId' => $productId],
            'yourPrice' => $yourPrice,
        ]);

        $response->throw();
        return $response->json();
    }

    /**
     * Update an existing offer
     */
    public function updateOffer(
        int $offerId,
        float $yourPrice,
        ?float $retailPrice = null,
        ?string $toggleOffer = null,
        ?int $declaredStock = null
    ): array {

        // basic validation
        if (empty($offerId) || empty($yourPrice)) {
            return ['error' => 'Offer id or your price is empty'];
        }

        // build payload only with non-empty values
        $payload = [
            'offerId'   => $offerId,
            'yourPrice' => $yourPrice,
        ];

        if (!empty($retailPrice)) {
            $payload['retailPrice'] = $retailPrice;
        }

        if (!empty($toggleOffer)) {
            $payload['toggleOffer'] = $toggleOffer; // "enable" or "disable"
        }

        if (!is_null($declaredStock)) {
            $payload['declaredStock'] = $declaredStock;
        }

        $response = $this->withAuth()->patch('/api/seller/legacy/offer/update', $payload);

        $response->throw();
        return $response->json();
    }

    /**
     * Enable / Disable an offer
     */
    public function toggleOffer(int $offerId, string $type = 'enable'): array
    {

        // if empty offer id return
        if (empty($offerId)) {
            return ['error' => 'Offer id is empty'];
        }

        $response = $this->withAuth()->put('/api/seller/legacy/offer/toggle', [
            'offerId' => $offerId,
            'type'    => $type, // "enable" or "disable"
        ]);

        $response->throw();
        return $response->json();
    }

    /**
     * Update offer price
     */
    public function updateOfferPrice(int $offerId, float $price): array
    {

        // if empty offer id or price return
        if (empty($offerId) || empty($price)) {
            return ['error' => 'Offer id or price is empty'];
        }

        $response = $this->withAuth()->put('/api/seller/legacy/offer/update-price', [
            'offerId' => $offerId,
            'price'   => $price,
        ]);

        $response->throw();
        return $response->json();
    }
}
