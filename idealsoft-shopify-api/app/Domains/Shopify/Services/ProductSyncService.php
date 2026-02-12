<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Services;

use App\Domains\Shopify\DTOs\ShopifyProductData;
use App\Domains\Shopify\Jobs\FetchShopifyProductsJob;
use App\Domains\Shopify\Models\Product;
use Illuminate\Support\Facades\DB;

final readonly class ProductSyncService
{
    public function dispatchFullSync(): void
    {
        FetchShopifyProductsJob::dispatch();
    }

    public function syncProduct(ShopifyProductData $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            return Product::query()->updateOrCreate(
                ['shopify_id' => $data->shopifyId],
                [
                    'title' => $data->title,
                    'description' => $data->description,
                    'handle' => $data->handle,
                    'vendor' => $data->vendor,
                    'product_type' => $data->productType,
                    'status' => $data->status,
                    'tags' => $data->tags,
                    'variants' => $data->variants,
                    'images' => $data->images,
                    'published_at' => $data->publishedAt,
                ],
            );
        });
    }

    public function deleteByShopifyId(string $shopifyId): bool
    {
        return (bool) Product::query()
            ->where('shopify_id', $shopifyId)
            ->delete();
    }
}
