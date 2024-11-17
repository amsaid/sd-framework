<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeListenerCommand extends Command
{
    protected string $name = 'make:listener';
    protected string $description = 'Create a new event listener class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Listener name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $event = $options['event'] ?? null;
        
        $listenerNamespace = "App\\Listeners";
        $listenerPath = app_path("Listeners");
        
        if (!is_dir($listenerPath)) {
            mkdir($listenerPath, 0755, true);
        }

        $listenerClass = ucfirst($name) . "Listener";
        $listenerFile = $listenerPath . DIRECTORY_SEPARATOR . $listenerClass . ".php";

        if (file_exists($listenerFile)) {
            $this->error("Listener {$listenerClass} already exists!");
            return self::FAILURE;
        }

        $eventType = $event ? "\\App\\Events\\{$event}" : 'object';
        
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$listenerNamespace};

class {$listenerClass}
{
    public function handle({$eventType} \$event): void
    {
        // Handle the event
    }
}
PHP;

        if (file_put_contents($listenerFile, $content)) {
            $this->output("Listener {$listenerClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create listener {$listenerClass}!");
        return self::FAILURE;
    }
}
