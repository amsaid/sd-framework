<?php

declare(strict_types=1);

namespace SdFramework\Console;

use SdFramework\Container\Container;
use SdFramework\Application as BaseApplication;
use SdFramework\Error\Handler;
use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\Routing\Router;
use SdFramework\ServiceProvider\ServiceProvider;
use SdFramework\Error\ApplicationException;
use SdFramework\Error\ContainerException;
use SdFramework\Error\HttpException;
use SdFramework\Config\Environment;
use SdFramework\Config\Config;
use SdFramework\Event\EventDispatcher;
use SdFramework\Event\Events\Application\ApplicationBooted;
use SdFramework\Event\Events\Application\ApplicationBooting;
use SdFramework\Event\Events\Http\RequestError;
use SdFramework\Event\Events\Http\RequestHandled;
use SdFramework\Event\Events\Http\RequestHandling;

class Application extends Container
{
    private array $commands = [];
    protected static ?Container $instance = null;
    private string $basePath;
    private array $coreProviders = [];
    private array $featureProviders = [];
    private array $loadedProviders = [];
    private bool $booted = false;
    private Handler $errorHandler;
    private Environment $env;
    private EventDispatcher $events;
    private Config $config;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        static::$instance = $this;

        try {
            $this->bootstrap();
        } catch (\Throwable $e) {
            $this->handleBootstrapError($e);
        }
    }

    private function bootstrap(): void
    {
        // Initialize environment
        $this->initializeEnvironment();

        // Register base bindings
        $this->registerBaseBindings();

        // Load configuration
        $this->initializeConfig();

        $this->registerCoreProviders();
        $this->bootProviders();
        // Register core commands
        $this->registerCoreCommands();
    }

    private function initializeEnvironment(): void
    {
        try {
            $this->env = new Environment();
            
            // Load .env file if it exists
            $envFile = $this->basePath . '/.env';
            if (file_exists($envFile)) {
                $this->env->load($envFile);
            } elseif (file_exists($this->basePath . '/.env.example')) {
                // Load example file as fallback in development
                $this->env->load($this->basePath . '/.env.example');
            }

            // Register environment in container
            $this->instance(Environment::class, $this->env);
            $this->alias(Environment::class, 'env');
        } catch (Throwable $e) {
            throw new ApplicationException(
                'Failed to initialize environment: ' . $e->getMessage()
            );
        }
    }

    private function registerBaseBindings(): void
    {
        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        $this->instance(self::class, $this);
    }

    private function initializeConfig(): void
    {
        try {
            $this->config = new \SdFramework\Config\Config($this->basePath . '/config');
            $this->instance(\SdFramework\Config\Config::class, $this->config);
            $this->alias(\SdFramework\Config\Config::class, 'config');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to initialize config: ' . $e->getMessage());
        }
    }

    private function registerCoreProviders(): void
    {
        $coreProviders = config('app.core_providers', []);        
        foreach ($coreProviders as $provider) {
            $this->registerServiceProvider($provider, $this->coreProviders);
        }
    }   

    private function registerServiceProvider(string $provider, array &$providers): void
    {
        try {
            if (isset($this->loadedProviders[$provider])) {
                return;
            }
            $providers[] = $provider;
            $providerInstance = new $provider($this);
            if (method_exists($providerInstance, 'register')) {
                $providerInstance->register();
            }
            $this->loadedProviders[$provider] = $providerInstance;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to register service provider [{$provider}]: " . $e->getMessage());
        }
    }

    private function bootProviders(): void
    {
        try {
            // Boot providers using stored instances
            foreach ($this->loadedProviders as $provider) {
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to boot providers: ' . $e->getMessage());
        }
    }

    private function registerCoreCommands(): void
    {
        $coreCommands = [
            \SdFramework\Console\Commands\ListCommand::class,
            \SdFramework\Console\Commands\ConfigCacheCommand::class,
            \SdFramework\Console\Commands\ConfigClearCommand::class,
            \SdFramework\Console\Commands\MakeMigrationCommand::class,
            \SdFramework\Console\Commands\MigrateCommand::class,
        ];

        foreach ($coreCommands as $commandClass) {
            if (class_exists($commandClass)) {
                $this->add(new $commandClass());
            }
        }
    }

    public function add(Command $command): void
    {
        $command->setApplication($this);
        $this->commands[$command->getName()] = $command;
    }

    public function run(array $argv = []): int
    {
        try {
            $command = $this->resolveCommand($argv);
            return $command->execute($argv);
        } catch (\Throwable $e) {
            $this->renderError($e);
            return 1;
        }
    }

    private function resolveCommand(array $argv): Command
    {
        $commandName = $argv[1] ?? 'list';

        if (!isset($this->commands[$commandName])) {
            throw new \InvalidArgumentException("Command not found: $commandName");
        }

        return $this->commands[$commandName];
    }

    private function renderError(\Throwable $e): void
    {
        fwrite(STDERR, sprintf(
            "Error: %s\nFile: %s:%d\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        if ($this->config->get('app.debug', false)) {
            fwrite(STDERR, $e->getTraceAsString() . "\n");
        }
    }

    private function handleBootstrapError(\Throwable $e): void
    {
        fwrite(STDERR, sprintf(
            "Fatal Error: %s\nFile: %s:%d\n",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
        exit(1);
    }

    public static function getInstance(): ?Container
    {
        return static::$instance;
    }

    public function isDevelopment(): bool
    {
        return $this->env->get('APP_ENV', 'production') === 'development';
    }

    public function environment(): Environment
    {
        return $this->env;
    }

    public function events(): EventDispatcher
    {
        return $this->events;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this;
    }
}
