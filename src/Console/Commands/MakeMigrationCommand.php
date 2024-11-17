<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use DateTime;

class MakeMigrationCommand extends Command
{
    protected string $name = 'make:migration';
    protected string $description = 'Create a new migration file';

    public function handle(array $arguments = [], array $options = []): int
    {
        try {
            if (empty($arguments)) {
                $this->error('Migration name not provided');
                return 1;
            }

            $name = $arguments[0];
            $timestamp = (new DateTime())->format('Y_m_d_His');
            $className = $this->getClassName($name);
            $path = $this->app->getBasePath() . '/database/migrations';
            
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
            $this->output("Created Migration: {$filename}");
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
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

return new class extends Migration
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
};

PHP;
    }
}
