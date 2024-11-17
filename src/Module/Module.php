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

    protected function makeCommand(string $name): string
    {
        $commandNamespace = "App\\Commands";
        $commandPath = app_path("Commands");
        
        if (!is_dir($commandPath)) {
            mkdir($commandPath, 0755, true);
        }

        $commandClass = ucfirst($name) . "Command";
        $commandFile = $commandPath . DIRECTORY_SEPARATOR . $commandClass . ".php";

        if (!file_exists($commandFile)) {
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$commandNamespace};

use SdFramework\Console\Command;

class {$commandClass} extends Command
{
    protected string \$signature = '{$name}';
    protected string \$description = 'Description of {$name} command';

    public function handle(): int
    {
        \$this->info('Command {$name} is running...');
        return self::SUCCESS;
    }
}
PHP;
            file_put_contents($commandFile, $content);
        }

        return $commandNamespace . "\\" . $commandClass;
    }

    protected function makeMiddleware(string $name): string
    {
        $middlewareNamespace = "App\\Middleware";
        $middlewarePath = app_path("Middleware");
        
        if (!is_dir($middlewarePath)) {
            mkdir($middlewarePath, 0755, true);
        }

        $middlewareClass = ucfirst($name) . "Middleware";
        $middlewareFile = $middlewarePath . DIRECTORY_SEPARATOR . $middlewareClass . ".php";

        if (!file_exists($middlewareFile)) {
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
            file_put_contents($middlewareFile, $content);
        }

        return $middlewareNamespace . "\\" . $middlewareClass;
    }

    protected function makeEvent(string $name): string
    {
        $eventNamespace = "App\\Events";
        $eventPath = app_path("Events");
        
        if (!is_dir($eventPath)) {
            mkdir($eventPath, 0755, true);
        }

        $eventClass = ucfirst($name) . "Event";
        $eventFile = $eventPath . DIRECTORY_SEPARATOR . $eventClass . ".php";

        if (!file_exists($eventFile)) {
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$eventNamespace};

class {$eventClass}
{
    public function __construct(
        public readonly array \$payload = []
    ) {}
}
PHP;
            file_put_contents($eventFile, $content);
        }

        return $eventNamespace . "\\" . $eventClass;
    }

    protected function makeListener(string $name, string $event): string
    {
        $listenerNamespace = "App\\Listeners";
        $listenerPath = app_path("Listeners");
        
        if (!is_dir($listenerPath)) {
            mkdir($listenerPath, 0755, true);
        }

        $listenerClass = ucfirst($name) . "Listener";
        $listenerFile = $listenerPath . DIRECTORY_SEPARATOR . $listenerClass . ".php";

        if (!file_exists($listenerFile)) {
            $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$listenerNamespace};

use {$event};

class {$listenerClass}
{
    public function handle({$event} \$event): void
    {
        // Handle the event here
    }
}
PHP;
            file_put_contents($listenerFile, $content);
        }

        return $listenerNamespace . "\\" . $listenerClass;
    }

    protected function makeProvider(string $name): string
    {
        $providerNamespace = "App\\Providers";
        $providerPath = app_path("Providers");
        
        if (!is_dir($providerPath)) {
            mkdir($providerPath, 0755, true);
        }

        $providerClass = ucfirst($name) . "ServiceProvider";
        $providerFile = $providerPath . DIRECTORY_SEPARATOR . $providerClass . ".php";

        if (!file_exists($providerFile)) {
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
            file_put_contents($providerFile, $content);
        }

        return $providerNamespace . "\\" . $providerClass;
    }

    protected function makeMigration(string $name): string
    {
        $migrationPath = database_path("migrations");
        
        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $migrationClass = ucfirst($name);
        $migrationFile = $migrationPath . DIRECTORY_SEPARATOR . "{$timestamp}_{$name}.php";

        if (!file_exists($migrationFile)) {
            $content = <<<PHP
<?php

declare(strict_types=1);

use SdFramework\Database\Migration;
use SdFramework\Database\Schema;
use SdFramework\Database\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$name}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$name}');
    }
};
PHP;
            file_put_contents($migrationFile, $content);
        }

        return $migrationFile;
    }

    protected function makeSeeder(string $name): string
    {
        $seederNamespace = "Database\\Seeders";
        $seederPath = database_path("seeders");
        
        if (!is_dir($seederPath)) {
            mkdir($seederPath, 0755, true);
        }

        $seederClass = ucfirst($name) . "Seeder";
        $seederFile = $seederPath . DIRECTORY_SEPARATOR . $seederClass . ".php";

        if (!file_exists($seederFile)) {
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
            file_put_contents($seederFile, $content);
        }

        return $seederNamespace . "\\" . $seederClass;
    }
}
