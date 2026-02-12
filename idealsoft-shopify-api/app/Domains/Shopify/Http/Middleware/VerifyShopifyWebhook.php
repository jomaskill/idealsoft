<?php

declare(strict_types=1);

namespace App\Domains\Shopify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyShopifyWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-SHA256');
        $secret = config('shopify.webhook_secret');

        if (! $hmacHeader || ! $secret) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $computedHmac = base64_encode(
            hash_hmac('sha256', $request->getContent(), $secret, true)
        );

        if (! hash_equals($computedHmac, $hmacHeader)) {
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
