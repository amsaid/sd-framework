<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper;

use SdFramework\Container\Container;

class HelperRegistry
{
    private Container $container;
    private array $helpers = [];
    private array $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(string $name, string $helperClass): void
    {
        if (!class_exists($helperClass)) {
            throw new \InvalidArgumentException("Helper class {$helperClass} does not exist");
        }

        if (!is_subclass_of($helperClass, Helper::class)) {
            throw new \InvalidArgumentException("Helper class must implement " . Helper::class);
        }

        $this->helpers[$name] = $helperClass;
    }

    public function get(string $name): Helper
    {
        if (!isset($this->helpers[$name])) {
            throw new \InvalidArgumentException("Helper {$name} is not registered");
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = $this->container->get($this->helpers[$name]);
        }

        return $this->instances[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->helpers[$name]);
    }

    public function getRegisteredHelpers(): array
    {
        return array_keys($this->helpers);
    }

    public function __call(string $name, array $arguments)
    {
        if (!$this->has($name)) {
            throw new \BadMethodCallException("Helper {$name} is not registered");
        }

        return $this->get($name)->handle(...$arguments);
    }
}
