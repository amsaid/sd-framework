<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use DateTime;

class MakeMigrationCommand extends Command
{
    protected string $signature = 'make:migration {name : The name of the migration}';
    protected string $description = 'Create a new migration file';

    public function handle(): int
    {
        $name = $this->argument('name');
        $timestamp = (new DateTime())->format('Y_m_d_His');
        $className = $this->getClassName($name);
        $path = $this->app->databasePath('migrations');
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $filename = $timestamp . '_' . $name . '.php';
        $filepath = $path . '/' . $filename;
        
        if (file_exists($filepath)) {
            $this->error('Migration already exists!');
            return 1;
        }
        
        file_put_contents($filepath, $this->getMigrationContent($className));
        $this->info("Created Migration: {$filename}");
        return 0;
    }

    private function getClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    private function getMigrationContent(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace Database\Migrations;

use SdFramework\Database\Migration;
use SdFramework\Database\Schema\Table;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->createTable('table_name', function (Table \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }
    
    public function down(): void
    {
        \$this->dropTable('table_name');
    }
}

PHP;
    }
}
