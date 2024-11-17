<?php

declare(strict_types=1);

namespace SdFramework\Config;

use SdFramework\Contract\Config\ConfigManagerInterface;

class ConfigManager implements ConfigManagerInterface
{
    private array $config = [];

    /**
     * Load configuration from a file
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Configuration file not found: {$path}");
        }

        $key = pathinfo($path, PATHINFO_FILENAME);
        $config = require $path;

        if (!is_array($config)) {
            throw new \RuntimeException("Configuration file must return an array: {$path}");
        }

        $this->config[$key] = $config;
    }

    /**
     * Get configuration value using dot notation
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $config = $this->config;

        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                return $default;
            }
            $config = $config[$part];
        }

        return $config;
    }

    /**
     * Set configuration value using dot notation
     */
    public function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $config = &$this->config;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $config[$part] = $value;
                break;
            }

            if (!isset($config[$part]) || !is_array($config[$part])) {
                $config[$part] = [];
            }

            $config = &$config[$part];
        }
    }

    /**
     * Check if configuration exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        return $this->config;
    }
}
