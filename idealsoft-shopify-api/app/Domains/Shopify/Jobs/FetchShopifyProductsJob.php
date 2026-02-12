<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Jobs;

use App\Domains\Shopify\Services\ShopifyClientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchShopifyProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly ?string $pageInfo = null,
    ) {}

    public function handle(ShopifyClientService $shopifyClient): void
    {
        $page = $shopifyClient->fetchProductsPage($this->pageInfo);

        foreach ($page['products'] as $rawProduct) {
            SyncProductJob::dispatch($rawProduct);
        }

        if ($page['nextPageInfo'] !== null) {
            self::dispatch($page['nextPageInfo']);
        }
    }
}
