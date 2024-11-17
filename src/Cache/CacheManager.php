<?php

namespace SdFramework\Cache;

use SdFramework\Cache\Store\FileStore;
use SdFramework\Cache\Store\MemoryStore;
use SdFramework\Cache\Store\RedisStore;
use SdFramework\Cache\Store\StoreInterface;
use SdFramework\Config\Config;

class CacheManager
{
    private Config $config;
    private array $stores = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function store(?string $name = null): StoreInterface
    {
        $name = $name ?? $this->getDefaultStore();

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->createStore($name);
        }

        return $this->stores[$name];
    }

    protected function createStore(string $name): StoreInterface
    {
        $config = $this->config->get("cache.stores.{$name}");
        
        if (!$config) {
            throw new \InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        return match($config['type']) {
            'file' => new FileStore(
                $config['path'],
                $config['extension'] ?? '.cache',
                $this->getSettings()
            ),
            'memory' => new MemoryStore(
                $config['limit'] ?? 1000,
                $this->getSettings()
            ),
            'redis' => new RedisStore(
                $config,
                $this->getSettings()
            ),
            default => throw new \InvalidArgumentException("Cache store type [{$config['type']}] is not supported.")
        };
    }

    protected function getDefaultStore(): string
    {
        return $this->config->get('cache.store', 'file');
    }

    protected function getSettings(): array
    {
        return $this->config->get('cache.settings', [
            'prefix' => 'sd_',
            'ttl' => 3600,
            'serialize' => true,
        ]);
    }
}
