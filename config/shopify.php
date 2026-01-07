<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Shopify Admin API integration.
    | Used for creating Draft Orders and payment links.
    |
    */

    'domain' => env('SHOPIFY_DOMAIN'),
    'client_id' => env('SHOPIFY_CLIENT_ID'),
    'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
    'api_version' => env('SHOPIFY_API_VERSION', '2025-01'),
];
