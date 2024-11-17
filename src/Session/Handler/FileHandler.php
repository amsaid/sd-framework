<?php

namespace SdFramework\Session\Handler;

use SessionHandlerInterface;

class FileHandler implements SessionHandlerInterface
{
    private string $path;
    private int $ttl;

    public function __construct(array $config)
    {
        $this->path = $config['path'];
        $this->ttl = ($config['lifetime'] ?? 120) * 60; // Convert minutes to seconds

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $file = $this->getFilePath($id);

        if (!file_exists($file)) {
            return '';
        }

        if (filemtime($file) < time() - $this->ttl) {
            unlink($file);
            return '';
        }

        return (string) file_get_contents($file);
    }

    public function write(string $id, string $data): bool
    {
        return file_put_contents($this->getFilePath($id), $data) !== false;
    }

    public function destroy(string $id): bool
    {
        $file = $this->getFilePath($id);
        
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $files = glob($this->path . DIRECTORY_SEPARATOR . 'sess_*');
        $count = 0;

        foreach ($files as $file) {
            if (filemtime($file) < time() - $max_lifetime) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    protected function getFilePath(string $id): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'sess_' . $id;
    }
}
