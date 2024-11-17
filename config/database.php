<?php

return [
    // Active connection
    'connection' => env('DB_CONNECTION', 'mysql'),

    // Connection profiles
    'profiles' => [
        'sqlite' => [
            'type' => 'sqlite',
            'file' => storage_path('database/sqlite/main.db'),
        ],
        
        'mysql' => [
            'type' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'name' => env('DB_DATABASE', 'sdframework'),
            'user' => env('DB_USERNAME', 'root'),
            'pass' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
        ],
    ],

    // Schema management
    'schema' => [
        'migrations_table' => 'schema_migrations',
        'migrations_path' => 'database/migrations',
        'backup_path' => storage_path('database/backups'),
    ],
];
