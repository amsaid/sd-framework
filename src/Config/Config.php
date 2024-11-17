<?php

declare(strict_types=1);

namespace SdFramework\Config;

class Config
{
    private array $config = [];
    private string $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->loadConfiguration();
    }

    private function loadConfiguration(): void
    {
        if (!is_dir($this->configPath)) {
            throw new \RuntimeException("Config directory does not exist: {$this->configPath}");
        }

        foreach (glob($this->configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            $config = require $file;
            if (is_array($config)) {
                $this->config[$name] = $config;
            }
        }
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }

    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $config[$key] = $value;
                break;
            }

            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function all(): array
    {
        return $this->config;
    }
}
