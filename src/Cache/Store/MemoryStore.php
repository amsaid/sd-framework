<?php

namespace SdFramework\Cache\Store;

class MemoryStore implements StoreInterface
{
    private array $items = [];
    private int $limit;
    private array $settings;

    public function __construct(int $limit, array $settings)
    {
        $this->limit = $limit;
        $this->settings = $settings;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->getKey($key);
        
        if (!isset($this->items[$key])) {
            return $default;
        }

        $item = $this->items[$key];
        
        if ($item['expiry'] !== 0 && $item['expiry'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $item['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $key = $this->getKey($key);
        $ttl = $ttl ?? $this->settings['ttl'];

        // Check memory limit
        if (!isset($this->items[$key]) && count($this->items) >= $this->limit) {
            return false;
        }

        $this->items[$key] = [
            'value' => $value,
            'expiry' => $ttl > 0 ? time() + $ttl : 0,
        ];

        return true;
    }

    public function delete(string $key): bool
    {
        $key = $this->getKey($key);
        unset($this->items[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->items = [];
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key, null) !== null;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        $current = $this->get($key, 0);
        if (!is_numeric($current)) {
            return false;
        }

        $new = $current + $value;
        return $this->set($key, $new) ? $new : false;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->increment($key, -$value);
    }

    public function many(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }
        return $values;
    }

    public function setMany(array $values, ?int $ttl = null): bool
    {
        if (count($this->items) + count($values) > $this->limit) {
            return false;
        }

        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMany(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    protected function getKey(string $key): string
    {
        return $this->settings['prefix'] . $key;
    }
}
