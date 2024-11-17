<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Module\ModuleManager;
use SdFramework\Config\Config;
use SdFramework\Event\EventDispatcher;
use SdFramework\Database\Connection;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('modules', function ($app) {
            return new ModuleManager(
                $app->get(Connection::class),
                $app->get(Config::class),
                $app->get(EventDispatcher::class)
            );
        });
    }
}
