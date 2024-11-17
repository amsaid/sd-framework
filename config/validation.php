<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Validation Rules
    |--------------------------------------------------------------------------
    |
    | This array contains the default validation rules that should be loaded
    | when the validation module boots. You can add your own custom rules
    | by adding them to this array.
    |
    */
    'rules' => [
        'required' => SdFramework\Validation\Rule\RequiredRule::class,
        'email' => SdFramework\Validation\Rule\EmailRule::class,
        // Add more rules here
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to override the default error messages.
    | This allows you to specify custom messages for specific fields.
    |
    */
    'custom_messages' => [
        // 'email.required' => 'We need your email address!',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Cache
    |--------------------------------------------------------------------------
    |
    | Here you may configure if and how validation rules and messages should
    | be cached. Setting this to true can improve performance in production
    | by reducing database queries.
    |
    */
    'cache' => [
        'enabled' => env('VALIDATION_CACHE_ENABLED', true),
        'ttl' => env('VALIDATION_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale that will be used for validation messages
    | when no specific locale is provided.
    |
    */
    'default_locale' => env('VALIDATION_DEFAULT_LOCALE', 'en'),
];
