<?php

use App\Domains\Shopify\Http\Controllers\ShopifyWebhookController;
use App\Domains\Shopify\Http\Middleware\VerifyShopifyWebhook;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/shopify', ShopifyWebhookController::class)
    ->middleware(VerifyShopifyWebhook::class)
    ->name('webhooks.shopify');
