<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Services;

use App\Domains\Shopify\DTOs\ShopifyProductData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyClientService
{
    private function baseUrl(): string
    {
        $domain = config('shopify.store_domain');
        $version = config('shopify.api_version');

        return "https://{$domain}/admin/api/{$version}";
    }

    private function headers(): array
    {
        return [
            'X-Shopify-Access-Token' => config('shopify.access_token'),
            'Content-Type' => 'application/json',
        ];
    }

    public function fetchProductsPage(?string $pageInfo = null): array
    {
        $query = ['limit' => 50];

        if ($pageInfo) {
            $query['page_info'] = $pageInfo;
        }

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl()}/products.json", $query);

        $response->throw();

        $rawProducts = $response->json('products', []);
        $nextPageInfo = $this->extractNextPageInfo($response->header('Link'));

        Log::info('Fetched products page from Shopify', [
            'count' => count($rawProducts),
            'has_next_page' => $nextPageInfo !== null,
        ]);

        return [
            'products' => $rawProducts,
            'nextPageInfo' => $nextPageInfo,
        ];
    }

    public function fetchProduct(string $shopifyId): ?ShopifyProductData
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl()}/products/{$shopifyId}.json");

        if ($response->failed()) {
            return null;
        }

        $product = $response->json('product');

        return $product ? ShopifyProductData::fromApiResponse($product) : null;
    }

    private function extractNextPageInfo(?string $linkHeader): ?string
    {
        if (! $linkHeader) {
            return null;
        }

        // Parse Link header for rel="next"
        if (preg_match('/<[^>]*page_info=([^&>]*)>[^,]*rel="next"/', $linkHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
