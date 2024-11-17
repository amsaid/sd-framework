<?php

declare(strict_types=1);

namespace SdFramework\Console;

class Application
{
    private array $commands = [];

    public function add(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function run(array $argv = []): int
    {
        $command = $argv[1] ?? 'list';
        $arguments = array_slice($argv, 2);
        $options = $this->parseOptions($arguments);

        if ($command === 'list') {
            return $this->listCommands();
        }

        if (!isset($this->commands[$command])) {
            echo "Command not found: $command\n";
            return 1;
        }

        return $this->commands[$command]->handle($arguments, $options);
    }

    private function listCommands(): int
    {
        echo "Available commands:\n\n";
        
        foreach ($this->commands as $name => $command) {
            echo sprintf("  %-20s %s\n", $name, $command->getDescription());
        }
        
        echo "\n";
        return 0;
    }

    private function parseOptions(array &$arguments): array
    {
        $options = [];
        
        foreach ($arguments as $i => $arg) {
            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                if (str_contains($option, '=')) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
                unset($arguments[$i]);
            }
        }
        
        $arguments = array_values($arguments);
        return $options;
    }
}
