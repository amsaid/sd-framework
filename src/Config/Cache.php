<?php

declare(strict_types=1);

namespace SdFramework\Config;

class Cache
{
    private string $cachePath;

    public function __construct(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function has(): bool
    {
        return file_exists($this->getCacheFilePath());
    }

    public function get(): array
    {
        if (!$this->has()) {
            return [];
        }

        return require $this->getCacheFilePath();
    }

    public function set(array $config): bool
    {
        $content = '<?php return ' . var_export($config, true) . ';';
        return (bool) file_put_contents($this->getCacheFilePath(), $content);
    }

    public function clear(): bool
    {
        if ($this->has()) {
            return unlink($this->getCacheFilePath());
        }

        return true;
    }

    private function getCacheFilePath(): string
    {
        return $this->cachePath . '/config.php';
    }
}
