<?php

declare(strict_types=1);

use App\Domains\Shopify\DTOs\ShopifyProductData;
use App\Domains\Shopify\Jobs\FetchShopifyProductsJob;
use App\Domains\Shopify\Services\ShopifyClientService;
use Illuminate\Support\Facades\Queue;

it('dispatches sync jobs via queue for full sync', function (): void {
    Queue::fake();

    $this->artisan('shopify:sync-products')->assertSuccessful();

    Queue::assertPushed(FetchShopifyProductsJob::class, 1);
});

it('syncs a single product by ID', function (): void {
    $mockClient = Mockery::mock(ShopifyClientService::class);
    $mockClient->shouldReceive('fetchProduct')
        ->with('12345')
        ->once()
        ->andReturn(ShopifyProductData::fromApiResponse([
            'id' => 12345,
            'title' => 'Single Product',
            'handle' => 'single-product',
            'status' => 'active',
            'tags' => '',
            'variants' => [],
            'images' => [],
        ]));

    $this->app->instance(ShopifyClientService::class, $mockClient);

    $this->artisan('shopify:sync-products --product-id=12345')
        ->assertSuccessful();
});

it('fails when product ID is not found', function (): void {
    $mockClient = Mockery::mock(ShopifyClientService::class);
    $mockClient->shouldReceive('fetchProduct')
        ->with('99999')
        ->once()
        ->andReturnNull();

    $this->app->instance(ShopifyClientService::class, $mockClient);

    $this->artisan('shopify:sync-products --product-id=99999')
        ->assertFailed();
});
