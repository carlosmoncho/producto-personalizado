<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        // Producción (configurar via FRONTEND_URL en .env)
        env('FRONTEND_URL'),

        // Desarrollo local
        'http://localhost:3000',       // React/Vue dev server
        'http://localhost:5173',       // Vite dev server
        'http://localhost:8080',       // Vue CLI dev server
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8080',
    ]),

    'allowed_origins_patterns' => [
        // Permitir cualquier subdominio de Vercel para preview deployments
        '#^https://.*\.vercel\.app$#',
        // Permitir cualquier subdominio de Render
        '#^https://.*\.onrender\.com$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    'max_age' => 0,

    'supports_credentials' => true,

];
