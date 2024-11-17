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
        try {
            /** @var Config $config */
            $config = app('config');
            $cachePath = $this->app->getBasePath() . '/bootstrap/cache/config.php';
            
            // Ensure cache directory exists
            $cacheDir = dirname($cachePath);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            // Get all config items
            $configArray = $config->all();

            // Create cache file content
            $content = '<?php return ' . var_export($configArray, true) . ';';

            // Write to cache file
            if (file_put_contents($cachePath, $content) === false) {
                throw new \RuntimeException("Failed to write config cache file.");
            }

            $this->output('Configuration cached successfully.');
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
