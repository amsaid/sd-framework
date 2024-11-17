<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeModelCommand extends Command
{
    protected string $name = 'make:model';
    protected string $description = 'Create a new model class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Model name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $modelNamespace = "App\\Models";
        $modelPath = app_path("Models");
        
        if (!is_dir($modelPath)) {
            mkdir($modelPath, 0755, true);
        }

        $modelClass = ucfirst($name);
        $modelFile = $modelPath . DIRECTORY_SEPARATOR . $modelClass . ".php";

        if (file_exists($modelFile)) {
            $this->error("Model {$modelClass} already exists!");
            return self::FAILURE;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$modelNamespace};

use SdFramework\Database\Model;

class {$modelClass} extends Model
{
    protected ?string \$table = null;
    protected array \$fillable = [];
    protected array \$hidden = [];
    protected array \$casts = [];
}
PHP;

        if (file_put_contents($modelFile, $content)) {
            $this->output("Model {$modelClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create model {$modelClass}!");
        return self::FAILURE;
    }
}
