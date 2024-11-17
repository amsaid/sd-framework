<?php

declare(strict_types=1);

namespace SdFramework\Module;

use SdFramework\Config\Config;
use SdFramework\Event\EventDispatcher;

abstract class Module
{
    protected Config $config;
    protected EventDispatcher $events;
    protected bool $isEnabled = false;

    public function __construct(Config $config, EventDispatcher $events)
    {
        $this->config = $config;
        $this->events = $events;
    }

    abstract public function getName(): string;
    
    abstract public function getVersion(): string;
    
    public function getDescription(): string
    {
        return '';
    }
    
    public function getDependencies(): array
    {
        return [];
    }

    public function boot(): void
    {
        // Override to add boot logic
    }

    public function enable(): void
    {
        $this->isEnabled = true;
        $this->onEnable();
    }

    public function disable(): void
    {
        $this->isEnabled = false;
        $this->onDisable();
    }

    protected function onEnable(): void
    {
        // Override to add enable logic
    }

    protected function onDisable(): void
    {
        // Override to add disable logic
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    protected function registerConfig(string $key, mixed $value): void
    {
        $this->config->set($this->getName() . '.' . $key, $value);
    }

    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get($this->getName() . '.' . $key, $default);
    }

    protected function subscribe(string $event, callable $handler): void
    {
        $this->events->subscribe($event, $handler);
    }

    protected function dispatch(string $event, array $payload = []): void
    {
        $this->events->dispatch($event, $payload);
    }
}
