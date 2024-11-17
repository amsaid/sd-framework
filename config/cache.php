<?php

return [
    // Active store
    'store' => env('CACHE_STORE', 'file'),

    // Store configurations
    'stores' => [
        'file' => [
            'type' => 'file',
            'path' => storage_path('cache/data'),
            'extension' => '.cache',
        ],
        
        'memory' => [
            'type' => 'memory',
            'limit' => env('CACHE_MEMORY_LIMIT', 1000),
        ],

        'redis' => [
            'type' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => env('REDIS_DB', 0),
            'timeout' => env('REDIS_TIMEOUT', 0.0),
            'retry_interval' => env('REDIS_RETRY_INTERVAL', 0),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 0.0),
        ],
    ],

    // Cache settings
    'settings' => [
        'prefix' => env('CACHE_PREFIX', 'sd_'),
        'ttl' => env('CACHE_TTL', 3600),
        'serialize' => true,
    ],
];
