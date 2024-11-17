<?php

declare(strict_types=1);

namespace SdFramework\Routing;

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\Container\Container;
use SdFramework\Middleware\MiddlewareStack;
use SdFramework\Error\NotFoundException;
class Router
{
    private array $collections = [];
    private Container $container;
    private MiddlewareStack $middleware;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->middleware = new MiddlewareStack();
    }

    public function addCollection(RouteCollection $collection): void
    {
        $this->collections[] = $collection;
    }

    public function dispatch(Request $request): Response
    {
        $route = $this->findRoute($request);
        if (!$route) {
            throw new NotFoundException('Route not found');
        }

        // Create middleware stack for this route
        $middlewareStack = clone $this->middleware;
        
        // Add route-specific middleware
        foreach ($route->getMiddleware() as $middleware) {
            $middlewareStack->add($this->container->get($middleware));
        }

        // Execute route through middleware stack
        return $middlewareStack->handle($request, function (Request $request) use ($route) {
            return $route->execute($request, $this->container);
        });
    }

    private function findRoute(Request $request): ?Route
    {
        
        foreach ($this->collections as $collection) {
            
            if ($route = $collection->match($request)) {
                return $route;
            }
        }

        return null;
    }

    public function getMiddleware(): MiddlewareStack
    {
        return $this->middleware;
    }
}
