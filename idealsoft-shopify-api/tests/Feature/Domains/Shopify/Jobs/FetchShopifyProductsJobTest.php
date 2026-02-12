<?php

declare(strict_types=1);

use App\Domains\Shopify\Jobs\FetchShopifyProductsJob;
use App\Domains\Shopify\Jobs\SyncProductJob;
use App\Domains\Shopify\Services\ShopifyClientService;
use Illuminate\Support\Facades\Queue;

it('dispatches SyncProductJob for each product on the page', function (): void {
    Queue::fake();

    $mockClient = Mockery::mock(ShopifyClientService::class);
    $mockClient->shouldReceive('fetchProductsPage')
        ->once()
        ->with(null)
        ->andReturn([
            'products' => [
                ['id' => 1, 'title' => 'Product A', 'handle' => 'a', 'tags' => '', 'variants' => [], 'images' => []],
                ['id' => 2, 'title' => 'Product B', 'handle' => 'b', 'tags' => '', 'variants' => [], 'images' => []],
            ],
            'nextPageQuery' => null,
        ]);

    $this->app->instance(ShopifyClientService::class, $mockClient);

    (new FetchShopifyProductsJob)->handle($mockClient);

    Queue::assertPushed(SyncProductJob::class, 2);
    Queue::assertNotPushed(FetchShopifyProductsJob::class);
});

it('self-chains to fetch the next page when more results exist', function (): void {
    Queue::fake();

    $nextPageQuery = ['page_info' => 'abc123', 'limit' => '50'];

    $mockClient = Mockery::mock(ShopifyClientService::class);
    $mockClient->shouldReceive('fetchProductsPage')
        ->once()
        ->with(null)
        ->andReturn([
            'products' => [
                ['id' => 1, 'title' => 'Product A', 'handle' => 'a', 'tags' => '', 'variants' => [], 'images' => []],
            ],
            'nextPageQuery' => $nextPageQuery,
        ]);

    $this->app->instance(ShopifyClientService::class, $mockClient);

    (new FetchShopifyProductsJob)->handle($mockClient);

    Queue::assertPushed(SyncProductJob::class, 1);
    Queue::assertPushed(FetchShopifyProductsJob::class, function (FetchShopifyProductsJob $job) use ($nextPageQuery): bool {
        return $job->query === $nextPageQuery;
    });
});

it('does not dispatch anything for an empty page', function (): void {
    Queue::fake();

    $mockClient = Mockery::mock(ShopifyClientService::class);
    $mockClient->shouldReceive('fetchProductsPage')
        ->once()
        ->andReturn([
            'products' => [],
            'nextPageQuery' => null,
        ]);

    $this->app->instance(ShopifyClientService::class, $mockClient);

    (new FetchShopifyProductsJob)->handle($mockClient);

    Queue::assertNotPushed(SyncProductJob::class);
    Queue::assertNotPushed(FetchShopifyProductsJob::class);
});
