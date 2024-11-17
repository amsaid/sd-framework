<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Database\Connection;
use SdFramework\Database\QueryBuilder;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->getContainer()->singleton(Connection::class, function ($container) {
            $config = $container->get('config')->get('database');
            $profile = $config['profiles'][$config['connection']] ?? null;
            
            if (!$profile) {
                throw new \RuntimeException("Database profile '{$config['connection']}' not found");
            }

            return new Connection([
                'driver' => $profile['type'],
                'host' => $profile['host'],
                'port' => $profile['port'],
                'database' => $profile['name'],
                'username' => $profile['user'],
                'password' => $profile['pass'],
            ]);
        });

        $this->app->getContainer()->singleton(QueryBuilder::class, function ($container) {
            return new QueryBuilder($container->get(Connection::class));
        });
    }

    public function boot(): void
    {
        // Set up any database event listeners or initializations
    }
}
