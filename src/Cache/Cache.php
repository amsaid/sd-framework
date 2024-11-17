<?php

declare(strict_types=1);

namespace SdFramework\Cache;

use SdFramework\Cache\Store\StoreInterface;

class Cache
{
    protected StoreInterface $store;

    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->store->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->store->delete($key);
    }

    public function clear(): bool
    {
        return $this->store->clear();
    }

    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    public function increment(string $key, int $value = 1): int|false
    {
        return $this->store->increment($key, $value);
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->store->decrement($key, $value);
    }

    public function many(array $keys): array
    {
        return $this->store->many($keys);
    }

    public function setMany(array $values, ?int $ttl = null): bool
    {
        return $this->store->setMany($values, $ttl);
    }

    public function deleteMany(array $keys): bool
    {
        return $this->store->deleteMany($keys);
    }

    public function store(?string $name = null): StoreInterface
    {
        if ($name === null) {
            return $this->store;
        }

        return app(CacheManager::class)->store($name);
    }
}
