<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeSeederCommand extends Command
{
    protected string $name = 'make:seeder';
    protected string $description = 'Create a new database seeder class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Seeder name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $seederNamespace = "App\\Database\\Seeders";
        $seederPath = app_path("Database/Seeders");
        
        if (!is_dir($seederPath)) {
            mkdir($seederPath, 0755, true);
        }

        $seederClass = ucfirst($name) . "Seeder";
        $seederFile = $seederPath . DIRECTORY_SEPARATOR . $seederClass . ".php";

        if (file_exists($seederFile)) {
            $this->error("Seeder {$seederClass} already exists!");
            return self::FAILURE;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$seederNamespace};

use SdFramework\Database\Seeder;

class {$seederClass} extends Seeder
{
    public function run(): void
    {
        // Add your seeder logic here
    }
}
PHP;

        if (file_put_contents($seederFile, $content)) {
            $this->output("Seeder {$seederClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create seeder {$seederClass}!");
        return self::FAILURE;
    }
}
