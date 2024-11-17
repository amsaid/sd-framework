<?php

declare(strict_types=1);

namespace SdFramework\Support\ServiceProvider;

use SdFramework\ServiceProvider\ServiceProvider;
use SdFramework\Support\Helper\HelperRegistry;
use SdFramework\Support\Helper\Implementations\ArrayHelper;
use SdFramework\Support\Helper\Implementations\StringHelper;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HelperRegistry::class, function ($app) {
            $registry = new HelperRegistry($app);
            
            // Register core helpers
            $registry->register('array', ArrayHelper::class);
            $registry->register('str', StringHelper::class);
            
            return $registry;
        });

        // Make helper registry available globally
        $this->app->alias(HelperRegistry::class, 'helpers');
    }

    public function boot(): void
    {
        // No boot actions needed
    }
}
