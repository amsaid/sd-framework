<?php

namespace SdFramework\Cache\Store;

use Redis;
use RedisException;

class RedisStore implements StoreInterface
{
    private Redis $redis;
    private array $settings;

    public function __construct(array $config, array $settings)
    {
        $this->settings = $settings;
        $this->redis = new Redis();
        
        try {
            $this->connect($config);
        } catch (RedisException $e) {
            throw new \RuntimeException('Failed to connect to Redis: ' . $e->getMessage());
        }
    }

    protected function connect(array $config): void
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $timeout = $config['timeout'] ?? 0.0;
        $retryInterval = $config['retry_interval'] ?? 0;
        $readTimeout = $config['read_timeout'] ?? 0.0;

        if (!$this->redis->connect($host, $port, $timeout, null, $retryInterval, $readTimeout)) {
            throw new RedisException('Could not connect to Redis server');
        }

        if (isset($config['password'])) {
            if (!$this->redis->auth($config['password'])) {
                throw new RedisException('Redis authentication failed');
            }
        }

        if (isset($config['database'])) {
            if (!$this->redis->select($config['database'])) {
                throw new RedisException('Redis database selection failed');
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->getKey($key));
        
        if ($value === false) {
            return $default;
        }

        return $this->settings['serialize'] ? unserialize($value) : $value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $key = $this->getKey($key);
        $ttl = $ttl ?? $this->settings['ttl'];
        $value = $this->settings['serialize'] ? serialize($value) : $value;

        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $value);
        }

        return $this->redis->set($key, $value);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($this->getKey($key)) > 0;
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->getKey($key)) > 0;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        return $value > 0 
            ? $this->redis->incrBy($this->getKey($key), $value)
            : false;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $value > 0 
            ? $this->redis->decrBy($this->getKey($key), $value)
            : false;
    }

    public function many(array $keys): array
    {
        $prefixedKeys = array_map([$this, 'getKey'], $keys);
        $values = $this->redis->mGet($prefixedKeys);
        
        $result = [];
        foreach ($keys as $i => $key) {
            $value = $values[$i];
            $result[$key] = $value === false ? null : 
                ($this->settings['serialize'] ? unserialize($value) : $value);
        }
        
        return $result;
    }

    public function setMany(array $values, ?int $ttl = null): bool
    {
        if (empty($values)) {
            return true;
        }

        $ttl = $ttl ?? $this->settings['ttl'];
        
        // If TTL is set, we need to use pipeline for setex
        if ($ttl > 0) {
            $pipe = $this->redis->multi(Redis::PIPELINE);
            
            foreach ($values as $key => $value) {
                $value = $this->settings['serialize'] ? serialize($value) : $value;
                $pipe->setex($this->getKey($key), $ttl, $value);
            }
            
            $results = $pipe->exec();
            return !in_array(false, $results, true);
        }

        // For no TTL, we can use mSet
        $data = [];
        foreach ($values as $key => $value) {
            $value = $this->settings['serialize'] ? serialize($value) : $value;
            $data[$this->getKey($key)] = $value;
        }
        
        return $this->redis->mSet($data);
    }

    public function deleteMany(array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        $prefixedKeys = array_map([$this, 'getKey'], $keys);
        return $this->redis->del($prefixedKeys) >= 0;
    }

    protected function getKey(string $key): string
    {
        return $this->settings['prefix'] . $key;
    }
}
