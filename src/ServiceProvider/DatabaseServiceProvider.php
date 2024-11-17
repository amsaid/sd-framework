<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Database\Connection;
use SdFramework\Database\QueryBuilder;
use SdFramework\Database\Migrator;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Connection
        $this->app->getContainer()->singleton(Connection::class, function ($container) {
            $config = config('database.profiles.' . config('database.connection'));
            return new Connection($config);
        });

        // Register QueryBuilder
        $this->app->getContainer()->singleton(QueryBuilder::class, function ($container) {
            return new QueryBuilder($container->get(Connection::class));
        });

        // Register Migrator
        $this->app->getContainer()->singleton(Migrator::class, function ($container) {
            return new Migrator($container->get(QueryBuilder::class));
        });
    }

    public function boot(): void
    {
        // Set up any database event listeners or initializations
    }
}
