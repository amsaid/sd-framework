<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use SdFramework\Config\Config;

class ConfigCacheCommand extends Command
{
    protected string $name = 'config:cache';
    protected string $description = 'Create a cache file for faster configuration loading';

    public function handle(array $arguments = [], array $options = []): int
    {
        $cachePath = storage_path('framework/cache');
        
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $config = new Config([], $cachePath);
        
        // Load all configuration files
        $configPath = config_path();
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            $config->set($name, require $file);
        }

        echo "Configuration cached successfully!\n";
        return 0;
    }
}
