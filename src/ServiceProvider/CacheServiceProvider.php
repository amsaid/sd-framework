<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Cache\CacheManager;
use SdFramework\Cache\Store\StoreInterface;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CacheManager::class, function ($app) {
            return new CacheManager($app->make('config'));
        });

        $this->app->singleton(StoreInterface::class, function ($app) {
            return $app->make(CacheManager::class)->store();
        });

        $this->app->alias(StoreInterface::class, 'cache');
    }
}
