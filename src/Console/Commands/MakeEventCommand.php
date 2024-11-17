<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeEventCommand extends Command
{
    protected string $name = 'make:event';
    protected string $description = 'Create a new event class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Event name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $eventNamespace = "App\\Events";
        $eventPath = app_path("Events");
        
        if (!is_dir($eventPath)) {
            mkdir($eventPath, 0755, true);
        }

        $eventClass = ucfirst($name) . "Event";
        $eventFile = $eventPath . DIRECTORY_SEPARATOR . $eventClass . ".php";

        if (file_exists($eventFile)) {
            $this->error("Event {$eventClass} already exists!");
            return self::FAILURE;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$eventNamespace};

class {$eventClass}
{
    public function __construct()
    {
        // Add event constructor properties here
    }
}
PHP;

        if (file_put_contents($eventFile, $content)) {
            $this->output("Event {$eventClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create event {$eventClass}!");
        return self::FAILURE;
    }
}
