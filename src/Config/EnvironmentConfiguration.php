<?php

declare(strict_types=1);

namespace SdFramework\Config;

class EnvironmentConfiguration
{
    private string $environment;
    private array $environmentPaths = [];

    public function __construct(string $environment = 'production')
    {
        $this->environment = $environment;
    }

    public function addPath(string $path): void
    {
        $this->environmentPaths[] = $path;
    }

    public function load(): array
    {
        $config = [];

        foreach ($this->environmentPaths as $path) {
            // Load base configuration
            $baseFile = $path . '/config.php';
            if (file_exists($baseFile)) {
                $config = array_merge($config, require $baseFile);
            }

            // Load environment-specific configuration
            $envFile = $path . '/config.' . $this->environment . '.php';
            if (file_exists($envFile)) {
                $envConfig = require $envFile;
                $config = $this->mergeConfig($config, $envConfig);
            }
        }

        return $config;
    }

    private function mergeConfig(array $original, array $override): array
    {
        $merged = $original;

        foreach ($override as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeConfig($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }
}
