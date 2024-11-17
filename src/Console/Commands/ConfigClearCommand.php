<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class ConfigClearCommand extends Command
{
    protected string $name = 'config:clear';
    protected string $description = 'Remove the configuration cache file';

    public function handle(array $arguments = [], array $options = []): int
    {
        try {
            $cachePath = $this->app->getBasePath() . '/bootstrap/cache/config.php';

            if (file_exists($cachePath)) {
                if (!unlink($cachePath)) {
                    throw new \RuntimeException("Failed to remove config cache file.");
                }
                $this->output('Configuration cache cleared successfully.');
            } else {
                $this->output('Configuration cache file not found.');
            }

            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
