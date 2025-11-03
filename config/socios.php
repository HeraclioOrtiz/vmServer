<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socios API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external Socios API integration
    |
    */

    'api' => [
        'base_url' => env('SOCIOS_API_BASE', 'https://clubvillamitre.com/api_back_socios'),
        'login' => env('SOCIOS_API_LOGIN', 'surtek'),
        'token' => env('SOCIOS_API_TOKEN'),
        'timeout' => env('SOCIOS_API_TIMEOUT', 15),
        'verify_ssl' => env('SOCIOS_API_VERIFY', true),
        'retry_attempts' => env('SOCIOS_API_RETRY', 2),
    ],

    'images' => [
        'base_url' => env('SOCIOS_IMG_BASE', 'https://clubvillamitre.com/images/socios'),
        'timeout' => env('SOCIOS_IMG_TIMEOUT', 10),
        'verify_ssl' => env('SOCIOS_IMG_VERIFY', true),
    ],

    'sync' => [
        'refresh_threshold_hours' => env('SOCIOS_REFRESH_HOURS', 24),
        'auto_refresh' => env('SOCIOS_AUTO_REFRESH', true),
        'auto_promote_on_login' => env('SOCIOS_AUTO_PROMOTE_ON_LOGIN', true),
    ],

    'cache' => [
        'enabled' => env('SOCIOS_CACHE_ENABLED', true),
        'ttl' => env('SOCIOS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'socios_api',
    ],
];
