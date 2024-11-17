<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;

class MakeControllerCommand extends Command
{
    protected string $name = 'make:controller';
    protected string $description = 'Create a new controller class';

    public function handle(array $arguments = [], array $options = []): int
    {
        if (empty($arguments)) {
            $this->error('Controller name is required!');
            return self::FAILURE;
        }

        $name = $arguments[0];
        $resource = isset($options['resource']);
        $controllerNamespace = "App\\Controllers";
        $controllerPath = app_path("Controllers");
        
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0755, true);
        }

        $controllerClass = ucfirst($name) . "Controller";
        $controllerFile = $controllerPath . DIRECTORY_SEPARATOR . $controllerClass . ".php";

        if (file_exists($controllerFile)) {
            $this->error("Controller {$controllerClass} already exists!");
            return self::FAILURE;
        }

        $methods = $resource ? $this->getResourceMethods() : $this->getBasicMethods();
        
        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$controllerNamespace};

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\Controller\Controller;

class {$controllerClass} extends Controller
{
{$methods}
}
PHP;

        if (file_put_contents($controllerFile, $content)) {
            $this->output("Controller {$controllerClass} created successfully.");
            return self::SUCCESS;
        }

        $this->error("Failed to create controller {$controllerClass}!");
        return self::FAILURE;
    }

    private function getResourceMethods(): string
    {
        return <<<'PHP'
    public function index(Request $request): Response
    {
        return $this->response->json(['message' => 'List all resources']);
    }

    public function store(Request $request): Response
    {
        return $this->response->json(['message' => 'Create a new resource']);
    }

    public function show(Request $request, int $id): Response
    {
        return $this->response->json(['message' => 'Show resource', 'id' => $id]);
    }

    public function update(Request $request, int $id): Response
    {
        return $this->response->json(['message' => 'Update resource', 'id' => $id]);
    }

    public function destroy(Request $request, int $id): Response
    {
        return $this->response->json(['message' => 'Delete resource', 'id' => $id]);
    }
PHP;
    }

    private function getBasicMethods(): string
    {
        return <<<'PHP'
    public function index(Request $request): Response
    {
        return $this->response->json(['message' => 'Hello World']);
    }
PHP;
    }
}
