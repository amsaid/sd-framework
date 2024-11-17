<?php

declare(strict_types=1);

namespace SdFramework\Module;

use SdFramework\Config\Config;
use SdFramework\Event\EventDispatcher;
use SdFramework\Container\Container;
abstract class Module
{
    protected Config $config;
    protected EventDispatcher $events;
    protected bool $isEnabled = false;
    protected bool $isBooted = false;
    protected Container $container;

    public function __construct(Config $config, EventDispatcher $events)
    {
        $this->config = $config;
        $this->events = $events;
        $this->container = app();
    }

    abstract public function getName(): string;
    
    abstract public function getVersion(): string;
    
    public function getDescription(): string
    {
        return '';
    }
    
    public function getDependencies(): array
    {
        return [];
    }

    public function boot(): void
    {
        if (!$this->isBooted) {
            $this->isBooted = true;
            $this->onBoot();
        }
    }

    protected function onBoot(): void
    {
        // Override to add boot logic
    }

    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    public function enable(): void
    {
        $this->isEnabled = true;
        $this->onEnable();
    }

    public function disable(): void
    {
        $this->isEnabled = false;
        $this->onDisable();
    }

    protected function onEnable(): void
    {
        // Override to add enable logic
    }

    protected function onDisable(): void
    {
        // Override to add disable logic
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    protected function registerConfig(string $key, mixed $value): void
    {
        $this->config->set($this->getName() . '.' . $key, $value);
    }

    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config->get($this->getName() . '.' . $key, $default);
    }

    protected function subscribe(string $event, callable $handler): void
    {
        $this->events->subscribe($event, $handler);
    }

    protected function dispatch(string $event, array $payload = []): void
    {
        $this->events->dispatch($event, $payload);
    }

    protected function makeModel(string $name): string
    {
        $modelNamespace = "App\\Models";
        $modelPath = app_path("Models");
        
        if (!is_dir($modelPath)) {
            mkdir($modelPath, 0755, true);
        }

        $modelClass = ucfirst($name);
        $modelFile = $modelPath . DIRECTORY_SEPARATOR . $modelClass . ".php";

        if (!file_exists($modelFile)) {
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
            file_put_contents($modelFile, $content);
        }

        return $modelNamespace . "\\" . $modelClass;
    }

    protected function makeController(string $name): string
    {
        $controllerNamespace = "App\\Controllers";
        $controllerPath = app_path("Controllers");
        
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath, 0755, true);
        }

        $controllerClass = ucfirst($name) . "Controller";
        $controllerFile = $controllerPath . DIRECTORY_SEPARATOR . $controllerClass . ".php";

        if (!file_exists($controllerFile)) {
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$controllerNamespace};

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\Controller\Controller;

class {$controllerClass} extends Controller
{
    public function index(Request \$request): Response
    {
        return \$this->response->json(['message' => 'Hello from {$controllerClass}']);
    }

    public function store(Request \$request): Response
    {
        return \$this->response->json(['message' => 'Store method not implemented']);
    }

    public function show(Request \$request, int \$id): Response
    {
        return \$this->response->json(['message' => 'Show method not implemented', 'id' => \$id]);
    }

    public function update(Request \$request, int \$id): Response
    {
        return \$this->response->json(['message' => 'Update method not implemented', 'id' => \$id]);
    }

    public function destroy(Request \$request, int \$id): Response
    {
        return \$this->response->json(['message' => 'Destroy method not implemented', 'id' => \$id]);
    }
}
PHP;
            file_put_contents($controllerFile, $content);
        }

        return $controllerNamespace . "\\" . $controllerClass;
    }
}
