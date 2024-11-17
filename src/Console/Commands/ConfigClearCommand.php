<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use SdFramework\Config\Config;

class ConfigClearCommand extends Command
{
    protected string $name = 'config:clear';
    protected string $description = 'Remove the configuration cache file';

    public function handle(array $arguments = [], array $options = []): int
    {
        $cachePath = storage_path('framework/cache');
        $config = new Config([], $cachePath);
        
        if ($config->clearCache()) {
            echo "Configuration cache cleared successfully!\n";
            return 0;
        }

        echo "No configuration cache found.\n";
        return 0;
    }
}
