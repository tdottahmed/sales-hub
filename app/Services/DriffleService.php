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

    /**
     * Request an access token from Driffle and cache it.
     * Token is cached for ~29 minutes by default.
     */
    public function getAccessToken(): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Driffle apiKey is not configured. Set credentials.driffle.api_key.');
        }

        $cacheKey = 'driffle.access_token.' . md5($this->apiKey);

        return Cache::remember($cacheKey, now()->addMinutes(29), function () {
            $response = Http::baseUrl($this->baseUrl)
                ->asJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('/api/seller/legacy/token', [
                    'apiKey' => $this->apiKey,
                ])
                ->throw();

            $json = $response->json() ?? [];

            // Try common token locations
            $token = $json['token']
                ?? ($json['data']['token'] ?? null)
                ?? ($json['access_token'] ?? null);

            if (!$token || !is_string($token)) {
                throw new \RuntimeException('Unable to retrieve token from Driffle token endpoint.');
            }

            return $token;
        });
    }

    /**
     * Fetch products via GET with pagination.
     * Example: /api/seller/legacy/products?page=3&limit=100
     */
    public function getProducts(int $page = 1, int $limit = 100): array
    {
        $token = $this->getAccessToken();

        $response = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withToken($token)
            ->get('/api/seller/legacy/products', [
                'page'  => $page,
                'limit' => $limit,
            ]);

        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * Simple iterator to go through all pages until fewer than $limit items are returned.
     */
    public function iterateAllProducts(int $limit = 100): \Generator
    {
        $page = 1;
        while (true) {
            $data = $this->getProducts($page, $limit);

            $items = $data['data'] ?? $data['items'] ?? $data['results'] ?? [];
            foreach ($items as $item) {
                yield $item;
            }

            if (count($items) < $limit) {
                break;
            }

            $page++;
        }
    }
}
