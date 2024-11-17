<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Session\SessionManager;

class SessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SessionManager::class, function ($app) {
            return new SessionManager($app->get('config'));
        });

        $this->app->alias(SessionManager::class, 'session');
    }

    public function boot(): void
    {
        $this->app->get(SessionManager::class)->start();
    }
}
