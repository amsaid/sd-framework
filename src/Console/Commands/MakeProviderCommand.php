<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeProviderCommand extends Command
{
    protected string $name = 'make:provider';
    protected string $description = 'Create a new service provider class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Provider name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $providerNamespace = "App\\Providers";
        $providerPath = app_path("Providers");
        
        if (!is_dir($providerPath)) {
            mkdir($providerPath, 0755, true);
        }

        $providerClass = ucfirst($name) . "ServiceProvider";
        $providerFile = $providerPath . DIRECTORY_SEPARATOR . $providerClass . ".php";

        if (file_exists($providerFile)) {
            $this->error("Provider {$providerClass} already exists!");
            return self::FAILURE;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$providerNamespace};

use SdFramework\ServiceProvider\ServiceProvider;

class {$providerClass} extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings in the container
    }

    public function boot(): void
    {
        // Bootstrap any application services
    }
}
PHP;

        if (file_put_contents($providerFile, $content)) {
            $this->output("Provider {$providerClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create provider {$providerClass}!");
        return self::FAILURE;
    }
}
