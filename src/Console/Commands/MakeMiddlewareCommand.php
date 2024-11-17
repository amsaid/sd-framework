<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeMiddlewareCommand extends Command
{
    protected string $name = 'make:middleware';
    protected string $description = 'Create a new middleware class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Middleware name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $middlewareNamespace = "App\\Middleware";
        $middlewarePath = app_path("Middleware");
        
        if (!is_dir($middlewarePath)) {
            mkdir($middlewarePath, 0755, true);
        }

        $middlewareClass = ucfirst($name) . "Middleware";
        $middlewareFile = $middlewarePath . DIRECTORY_SEPARATOR . $middlewareClass . ".php";

        if (file_exists($middlewareFile)) {
            $this->error("Middleware {$middlewareClass} already exists!");
            return self::FAILURE;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$middlewareNamespace};

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\Middleware\Middleware;

class {$middlewareClass} implements Middleware
{
    public function handle(Request \$request, callable \$next): Response
    {
        // Add your middleware logic here
        return \$next(\$request);
    }
}
PHP;

        if (file_put_contents($middlewareFile, $content)) {
            $this->output("Middleware {$middlewareClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create middleware {$middlewareClass}!");
        return self::FAILURE;
    }
}
