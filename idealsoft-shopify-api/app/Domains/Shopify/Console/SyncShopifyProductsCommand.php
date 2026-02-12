<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Console;

use App\Domains\Shopify\Services\ProductSyncService;
use App\Domains\Shopify\Services\ShopifyClientService;
use Illuminate\Console\Command;

final class SyncShopifyProductsCommand extends Command
{
    protected $signature = 'shopify:sync-products
                            {--product-id= : Sync a specific product by Shopify ID}';

    protected $description = 'Sync products from Shopify Admin API into the local database';

    public function handle(ProductSyncService $syncService): int
    {
        $productId = $this->option('product-id');

        if ($productId) {
            return $this->syncSingleProduct($productId, $syncService);
        }

        return $this->dispatchFullSync($syncService);
    }

    private function syncSingleProduct(string $productId, ProductSyncService $syncService): int
    {
        $this->info("Syncing product #{$productId}...");

        $shopifyClient = app(ShopifyClientService::class);
        $productData = $shopifyClient->fetchProduct($productId);

        if (! $productData) {
            $this->error("Product #{$productId} not found on Shopify.");

            return self::FAILURE;
        }

        $syncService->syncProduct($productData);
        $this->info('✓ Product synced successfully.');

        return self::SUCCESS;
    }

    private function dispatchFullSync(ProductSyncService $syncService): int
    {
        $this->info('Dispatching full product sync to queue...');
        $this->newLine();

        $syncService->dispatchFullSync();

        $this->info('✓ Sync jobs dispatched. Products will be synced in the background.');
        $this->info('  Monitor progress with: php artisan queue:work');

        return self::SUCCESS;
    }
}
