<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Auth\Auth;
use SdFramework\Database\Connection;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->getContainer()->singleton(Auth::class, function ($container) {
            return new Auth($container->get(Connection::class));
        });
    }
}
