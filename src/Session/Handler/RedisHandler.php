<?php

namespace SdFramework\Session\Handler;

use Redis;
use RedisException;
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{
    private Redis $redis;
    private int $ttl;
    private string $prefix;

    public function __construct(array $config)
    {
        $this->ttl = $config['lifetime'] ?? 120;
        $this->prefix = $config['prefix'] ?? 'sd_session:';
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

        if (isset($config['password']) && $config['password'] !== null) {
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

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        try {
            return $this->redis->close();
        } catch (RedisException) {
            return false;
        }
    }

    public function read(string $id): string|false
    {
        try {
            $data = $this->redis->get($this->prefix . $id);
            return $data === false ? '' : $data;
        } catch (RedisException) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            return $this->redis->setex(
                $this->prefix . $id,
                $this->ttl * 60, // Convert minutes to seconds
                $data
            );
        } catch (RedisException) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            return $this->redis->del($this->prefix . $id) > 0;
        } catch (RedisException) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        // Redis automatically removes expired keys
        return 0;
    }

    protected function getKey(string $id): string
    {
        return $this->prefix . $id;
    }
}
