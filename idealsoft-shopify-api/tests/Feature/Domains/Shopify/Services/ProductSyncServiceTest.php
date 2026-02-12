<?php

declare(strict_types=1);

use App\Domains\Shopify\DTOs\ShopifyProductData;
use App\Domains\Shopify\Jobs\FetchShopifyProductsJob;
use App\Domains\Shopify\Models\Product;
use App\Domains\Shopify\Services\ProductSyncService;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->syncService = app(ProductSyncService::class);
});

it('creates a new product from a DTO', function (): void {
    $dto = ShopifyProductData::fromApiResponse(sampleShopifyProduct());

    $product = $this->syncService->syncProduct($dto);

    expect($product->wasRecentlyCreated)->toBeTrue()
        ->and($product->shopify_id)->toBe('9999999')
        ->and($product->title)->toBe('Integration Test Product')
        ->and($product->status->value)->toBe('active')
        ->and($product->variants)->toHaveCount(2)
        ->and($product->images)->toHaveCount(1);
});

it('updates an existing product on re-sync', function (): void {
    $dto = ShopifyProductData::fromApiResponse(sampleShopifyProduct());

    $this->syncService->syncProduct($dto);
    expect(Product::count())->toBe(1);

    $updatedData = sampleShopifyProduct();
    $updatedData['title'] = 'Updated Title';
    $updatedDto = ShopifyProductData::fromApiResponse($updatedData);

    $product = $this->syncService->syncProduct($updatedDto);

    expect($product->wasRecentlyCreated)->toBeFalse()
        ->and($product->title)->toBe('Updated Title')
        ->and(Product::count())->toBe(1);
});

it('dispatches FetchShopifyProductsJob for full sync', function (): void {
    Queue::fake();

    $this->syncService->dispatchFullSync();

    Queue::assertPushed(FetchShopifyProductsJob::class, 1);
});

it('deletes a product by Shopify ID', function (): void {
    Product::factory()->create(['shopify_id' => '12345']);

    expect(Product::count())->toBe(1);

    $deleted = $this->syncService->deleteByShopifyId('12345');

    expect($deleted)->toBeTrue()
        ->and(Product::count())->toBe(0);
});

it('returns false when deleting a non-existent product', function (): void {
    $deleted = $this->syncService->deleteByShopifyId('non-existent');

    expect($deleted)->toBeFalse();
});

function sampleShopifyProduct(string $id = '9999999', string $title = 'Integration Test Product'): array
{
    return [
        'id' => $id,
        'title' => $title,
        'body_html' => '<p>Test description</p>',
        'handle' => str($title)->slug()->toString(),
        'vendor' => 'Test Vendor',
        'product_type' => 'Widget',
        'status' => 'active',
        'tags' => 'test, integration',
        'published_at' => '2024-06-01T12:00:00-04:00',
        'variants' => [
            ['title' => 'Small', 'sku' => 'W-SM', 'price' => '19.99', 'compare_at_price' => '24.99', 'inventory_quantity' => 50],
            ['title' => 'Large', 'sku' => 'W-LG', 'price' => '24.99', 'compare_at_price' => null, 'inventory_quantity' => 30],
        ],
        'images' => [
            ['src' => 'https://cdn.shopify.com/test.jpg', 'alt' => $title, 'position' => 1, 'width' => 640, 'height' => 480],
        ],
    ];
}
