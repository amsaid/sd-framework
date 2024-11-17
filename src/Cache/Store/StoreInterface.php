<?php

namespace SdFramework\Cache\Store;

interface StoreInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
    public function increment(string $key, int $value = 1): int|false;
    public function decrement(string $key, int $value = 1): int|false;
    public function many(array $keys): array;
    public function setMany(array $values, ?int $ttl = null): bool;
    public function deleteMany(array $keys): bool;
}
