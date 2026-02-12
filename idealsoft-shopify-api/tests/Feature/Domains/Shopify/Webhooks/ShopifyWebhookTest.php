<?php

declare(strict_types=1);

use App\Domains\Shopify\Models\Product;

it('rejects requests without HMAC header', function (): void {
    $this->postJson('/api/webhooks/shopify', ['id' => 1])
        ->assertUnauthorized();
});

it('rejects requests with invalid HMAC signature', function (): void {
    $this->postJson('/api/webhooks/shopify', ['id' => 1], [
        'X-Shopify-Hmac-SHA256' => 'invalid-hmac',
        'X-Shopify-Topic' => 'products/update',
    ])->assertUnauthorized();
});

it('accepts requests with valid HMAC signature for product update', function (): void {
    $payload = json_encode(['id' => 123456, 'title' => 'Test Product']);
    $secret = 'test-webhook-secret';

    config(['shopify.webhook_secret' => $secret]);

    $hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $this->call('POST', '/api/webhooks/shopify', [], [], [], [
        'HTTP_X_SHOPIFY_HMAC_SHA256' => $hmac,
        'HTTP_X_SHOPIFY_TOPIC' => 'products/update',
        'CONTENT_TYPE' => 'application/json',
    ], $payload)
        ->assertSuccessful();
});

it('handles product delete webhook', function (): void {
    Product::factory()->create(['shopify_id' => '789']);

    $payload = json_encode(['id' => 789]);
    $secret = 'test-webhook-secret';

    config(['shopify.webhook_secret' => $secret]);

    $hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $this->call('POST', '/api/webhooks/shopify', [], [], [], [
        'HTTP_X_SHOPIFY_HMAC_SHA256' => $hmac,
        'HTTP_X_SHOPIFY_TOPIC' => 'products/delete',
        'CONTENT_TYPE' => 'application/json',
    ], $payload)
        ->assertSuccessful();

    expect(Product::where('shopify_id', '789')->exists())->toBeFalse();
});

it('returns OK for unhandled webhook topics', function (): void {
    $payload = json_encode(['id' => 1]);
    $secret = 'test-webhook-secret';

    config(['shopify.webhook_secret' => $secret]);

    $hmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

    $this->call('POST', '/api/webhooks/shopify', [], [], [], [
        'HTTP_X_SHOPIFY_HMAC_SHA256' => $hmac,
        'HTTP_X_SHOPIFY_TOPIC' => 'orders/create',
        'CONTENT_TYPE' => 'application/json',
    ], $payload)
        ->assertSuccessful()
        ->assertJson(['message' => 'Unhandled topic']);
});
