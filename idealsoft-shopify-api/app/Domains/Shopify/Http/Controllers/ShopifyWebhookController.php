<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Http\Controllers;

use App\Domains\Shopify\Jobs\SyncProductJob;
use App\Domains\Shopify\Services\ProductSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ShopifyWebhookController
{
    /**
     * Handle incoming Shopify product webhooks.
     *
     * Supports topics: products/create, products/update, products/delete
     */
    public function __invoke(Request $request, ProductSyncService $syncService): JsonResponse
    {
        $topic = $request->header('X-Shopify-Topic');
        $payload = $request->all();

        Log::info('Shopify webhook received', ['topic' => $topic]);

        return match ($topic) {
            'products/create', 'products/update' => $this->handleProductSync($payload),
            'products/delete' => $this->handleProductDelete($payload, $syncService),
            default => response()->json(['message' => 'Unhandled topic'], Response::HTTP_OK),
        };
    }

    private function handleProductSync(array $payload): JsonResponse
    {
        SyncProductJob::dispatch($payload);

        return response()->json(['message' => 'Queued for sync'], Response::HTTP_OK);
    }

    private function handleProductDelete(array $payload, ProductSyncService $syncService): JsonResponse
    {
        $shopifyId = (string) ($payload['id'] ?? '');

        if ($shopifyId === '') {
            return response()->json(['error' => 'Missing product ID'], Response::HTTP_BAD_REQUEST);
        }

        $syncService->deleteByShopifyId($shopifyId);

        return response()->json(['message' => 'Product deleted'], Response::HTTP_OK);
    }
}
