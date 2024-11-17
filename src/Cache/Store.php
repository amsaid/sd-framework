<?php

declare(strict_types=1);

namespace SdFramework\Cache;

interface Store
{
    public function get(string $key, mixed $default = null): mixed;
    
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    
    public function delete(string $key): bool;
    
    public function clear(): bool;
    
    public function has(string $key): bool;
    
    public function increment(string $key, int $value = 1): int|false;
    
    public function decrement(string $key, int $value = 1): int|false;
    
    public function forever(string $key, mixed $value): bool;
    
    public function forget(string $key): bool;
    
    public function tags(array $names): static;
}
