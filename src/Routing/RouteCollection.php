<?php

declare(strict_types=1);

namespace SdFramework\Routing;

use SdFramework\Http\Request;

class RouteCollection
{
    private array $routes = [];
    private string $prefix = '';
    private array $middleware = [];
    private ?string $namespace = null;
    private array $attributes = [];

    public function add(Route $route): self
    {
        $route->setPrefix($this->prefix);
        
        if ($this->namespace) {
            $route->setNamespace($this->namespace);
        }
        
        foreach ($this->middleware as $middleware) {
            $route->addMiddleware($middleware);
        }
        
        foreach ($this->attributes as $key => $value) {
            $route->setAttribute($key, $value);
        }
        
        $this->routes[] = $route;
        
        return $this;
    }

    public function group(array $attributes, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;
        $previousNamespace = $this->namespace;
        $previousAttributes = $this->attributes;

        // Merge group attributes
        $this->prefix .= $attributes['prefix'] ?? '';
        $this->middleware = array_merge($this->middleware, $attributes['middleware'] ?? []);
        $this->namespace = $attributes['namespace'] ?? $this->namespace;
        $this->attributes = array_merge($this->attributes, $attributes);

        // Execute the group callback
        $callback($this);

        // Restore previous state
        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
        $this->namespace = $previousNamespace;
        $this->attributes = $previousAttributes;

        return $this;
    }

    public function match(Request $request): ?Route
    {
        $path = $request->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route->matches($path, $method)) {
                return $route;
            }
        }

        return null;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function get(string $path, $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, $handler): Route
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): Route
    {
        $route = new Route($method, $path, $handler);
        $this->add($route);
        return $route;
    }
}
