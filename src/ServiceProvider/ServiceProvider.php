<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Application;

abstract class ServiceProvider
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Create a new service provider instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services.
     */
    abstract public function register(): void;

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Optional boot method
    }

    /**
     * Get the container instance
     */
    protected function getContainer()
    {
        return $this->app->getContainer();
    }
}
