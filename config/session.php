<?php

return [
    // Session driver (file, redis)
    'driver' => env('SESSION_DRIVER', 'file'),

    // Session lifetime in minutes
    'lifetime' => env('SESSION_LIFETIME', 120),

    // Session cookie name
    'cookie' => env('SESSION_COOKIE', 'sd_session'),

    // Session cookie path
    'path' => '/',

    // Session cookie domain
    'domain' => env('SESSION_DOMAIN'),

    // Session cookie secure
    'secure' => env('SESSION_SECURE', false),

    // Session cookie HTTP only
    'http_only' => true,

    // Session cookie same site
    'same_site' => 'lax',

    // Session handlers configuration
    'handlers' => [
        'file' => [
            'path' => storage_path('sessions'),
        ],
        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => env('REDIS_SESSION_DB', 1), // Different DB than cache
            'prefix' => 'sd_session:',
            'timeout' => env('REDIS_TIMEOUT', 0.0),
            'retry_interval' => env('REDIS_RETRY_INTERVAL', 0),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 0.0),
        ],
    ],
];
