<?php

return [
    'default' => env('LOG_CHANNEL', 'app'),
    
    'path' => storage_path('logs'),
    
    'channels' => [
        'app' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        
        'error' => [
            'driver' => 'daily',
            'path' => storage_path('logs/error.log'),
            'level' => 'error',
            'days' => 30,
        ],
    ],
];
