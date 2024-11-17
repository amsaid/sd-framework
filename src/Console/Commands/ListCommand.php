<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class ListCommand extends Command
{
    protected string $name = 'list';
    protected string $description = 'List all available commands';

    public function handle(array $arguments = [], array $options = []): int
    {
        try {
            $commands = $this->app->getCommands();

            $this->output("\nAvailable commands:");
            $this->output(str_repeat('-', 50));

            // Get the longest command name for padding
            $maxLength = 0;
            foreach ($commands as $command) {
                $maxLength = max($maxLength, strlen($command->getName()));
            }

            // Sort commands alphabetically
            uasort($commands, function ($a, $b) {
                return strcmp($a->getName(), $b->getName());
            });

            foreach ($commands as $command) {
                $name = str_pad($command->getName(), $maxLength + 4);
                $this->output("  $name{$command->getDescription()}");
            }

            $this->output("\n");
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
