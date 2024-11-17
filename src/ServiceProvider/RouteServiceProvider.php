<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Routing\RouteCollection;

class RouteServiceProvider extends ServiceProvider
{
    protected string $namespace = 'App\\Controllers';

    public function register(): void
    {
        $this->app->singleton('routes.web', function () {
            return new RouteCollection();
        });

        $this->app->singleton('routes.api', function () {
            return new RouteCollection();
        });
    }

    public function boot(): void
    {
        $this->loadRoutes();
    }

    protected function loadRoutes(): void
    {
        // Load web routes
        $webRoutes = $this->app->make('routes.web');
        $webRoutes->group(['namespace' => $this->namespace], function (RouteCollection $routes) {
            $routePath = APP_PATH . '/Routes/web.php';
            if (file_exists($routePath)) {
                $rt = require $routePath;
                $rt($routes);
            }
        });

        // Load API routes
        $apiRoutes = $this->app->make('routes.api');
        $apiRoutes->group(['namespace' => $this->namespace], function (RouteCollection $routes) {
            $routePath = APP_PATH . '/Routes/api.php';
            if (file_exists($routePath)) {
                $rt = require $routePath;
                $rt($routes);
            }
        });

        // Register route collections with the router
        $router = $this->app->getRouter();
        $router->addCollection($webRoutes);
        $router->addCollection($apiRoutes);
    }
}
