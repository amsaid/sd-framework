<?php

namespace SdFramework\Cache\Store;

class FileStore implements StoreInterface
{
    private string $path;
    private string $extension;
    private array $settings;

    public function __construct(string $path, string $extension, array $settings)
    {
        $this->path = rtrim($path, '/');
        $this->extension = $extension;
        $this->settings = $settings;

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path = $this->getPath($key);
        
        if (!is_file($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $default;
        }

        $data = $this->settings['serialize'] ? unserialize($content) : $content;
        
        if (!is_array($data) || !isset($data['value']) || !isset($data['expiry'])) {
            return $default;
        }

        if ($data['expiry'] !== 0 && $data['expiry'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $path = $this->getPath($key);
        $ttl = $ttl ?? $this->settings['ttl'];
        
        $data = [
            'value' => $value,
            'expiry' => $ttl > 0 ? time() + $ttl : 0,
        ];

        $content = $this->settings['serialize'] ? serialize($data) : $data;
        
        return file_put_contents($path, $content) !== false;
    }

    public function delete(string $key): bool
    {
        $path = $this->getPath($key);
        
        if (is_file($path)) {
            return unlink($path);
        }

        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->path . '/*' . $this->extension);
        
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

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
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    protected function getPath(string $key): string
    {
        $hash = sha1($this->settings['prefix'] . $key);
        return $this->path . '/' . $hash . $this->extension;
    }
}
