<?php

return [
    'store_domain' => env('SHOPIFY_STORE_DOMAIN'),
    'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),
    'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
];
