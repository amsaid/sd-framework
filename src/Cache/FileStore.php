<?php

declare(strict_types=1);

namespace SdFramework\Cache;

use RuntimeException;

class FileStore implements Store
{
    private string $directory;
    private array $tags = [];

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '/');
        
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path = $this->path($key);
        
        if (!file_exists($path)) {
            return $default;
        }

        $contents = file_get_contents($path);
        $data = unserialize($contents);
        
        if ($data === false) {
            return $default;
        }

        if (isset($data['expiration']) && time() >= $data['expiration']) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $path = $this->path($key);
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $data = [
            'value' => $value,
            'expiration' => $ttl ? time() + $ttl : null,
            'tags' => $this->tags,
        ];

        $result = file_put_contents($path, serialize($data), LOCK_EX);
        
        $this->tags = [];
        
        return $result !== false;
    }

    public function delete(string $key): bool
    {
        return $this->forget($key);
    }

    public function clear(): bool
    {
        $files = glob($this->directory . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment(string $key, int $value = 1): int|false
    {
        $current = (int) $this->get($key, 0);
        $new = $current + $value;
        
        if ($this->set($key, $new)) {
            return $new;
        }

        return false;
    }

    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->increment($key, -$value);
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->set($key, $value);
    }

    public function forget(string $key): bool
    {
        $path = $this->path($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    public function tags(array $names): static
    {
        $this->tags = array_merge($this->tags, $names);
        return $this;
    }

    private function path(string $key): string
    {
        $parts = array_slice(str_split(sha1($key), 2), 0, 2);
        return $this->directory . '/' . implode('/', $parts) . '/' . sha1($key);
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new RuntimeException("Unable to create cache directory: {$path}");
            }
        }
    }
}
