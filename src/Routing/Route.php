<?php

declare(strict_types=1);

namespace SdFramework\Routing;

use SdFramework\Http\Request;
use SdFramework\Container\Container;

class Route
{
    private string $method;
    private string $path;
    private $handler;
    private array $parameters = [];
    private array $middleware = [];
    private string $prefix = '';
    private ?string $namespace = null;
    private array $attributes = [];
    private array $patterns = [];

    public function __construct(string $method, string $path, $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
    }

    public function matches(string $path, string $method): bool
    {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        $pattern = $this->buildPattern();
        if (preg_match($pattern, $path, $matches)) {
            // Extract named parameters
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $this->parameters[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    private function buildPattern(): string
    {
        $path = $this->prefix . $this->path;
        $pattern = preg_replace('/\{([a-zA-Z][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        
        // Apply custom patterns
        foreach ($this->patterns as $param => $customPattern) {
            $pattern = str_replace("(?P<{$param}>[^/]+)", "(?P<{$param}>{$customPattern})", $pattern);
        }
        
        return '#^' . $pattern . '$#';
    }

    public function execute(Request $request, Container $container)
    {
        if (is_string($this->handler)) {
            if ($this->namespace) {
                $this->handler = $this->namespace . '\\' . $this->handler;
            }

            if (str_contains($this->handler, '@')) {
                [$class, $method] = explode('@', $this->handler);
                $controller = $container->get($class);
                return $controller->$method($request, ...$this->parameters);
            }

            $handler = $container->get($this->handler);
            return $handler($request, ...$this->parameters);
        }

        return ($this->handler)($request, ...$this->parameters);
    }

    public function where(string $parameter, string $pattern): self
    {
        $this->patterns[$parameter] = $pattern;
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        foreach ($middleware as $m) {
            $this->addMiddleware($m);
        }
        return $this;
    }

    public function addMiddleware(string $middleware): void
    {
        if (!in_array($middleware, $this->middleware)) {
            $this->middleware[] = $middleware;
        }
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getPath(): string
    {
        return $this->prefix . $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
