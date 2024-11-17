<?php

return [
    // Basic settings
    'name' => env('APP_NAME', 'SdFramework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),

    // Core providers
    'core_providers' => [
        \SdFramework\ServiceProvider\LogServiceProvider::class,
        \SdFramework\ServiceProvider\EventServiceProvider::class,
        \SdFramework\Support\ServiceProvider\HelperServiceProvider::class,
        \SdFramework\ServiceProvider\DatabaseServiceProvider::class,
        \SdFramework\Error\ServiceProvider\ErrorServiceProvider::class,
      
        // Add more core providers here
    ],

    // Feature providers - optional based on needs
    'providers' => [
        \SdFramework\ServiceProvider\ModuleServiceProvider::class,
        \SdFramework\ServiceProvider\RouteServiceProvider::class,
        \SdFramework\ServiceProvider\ValidationServiceProvider::class,
        \SdFramework\ServiceProvider\SessionServiceProvider::class,
        \SdFramework\ServiceProvider\CacheServiceProvider::class,
    ],

    // Error handling
    'error' => [
        'reporting' => env('ERROR_REPORTING', E_ALL),
        'display' => env('DISPLAY_ERRORS', false),
        'log' => env('LOG_ERRORS', true),
        'log_file' => 'error.log',
    ],

    // Development settings
    'dev' => [
        'allow_debug' => env('DEV_ALLOW_DEBUG', env('APP_DEBUG', false)),
        'profiler' => env('DEV_PROFILER', false),
        'strict_mode' => env('DEV_STRICT_MODE', true),
    ],
];
