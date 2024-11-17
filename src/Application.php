<?php

declare(strict_types=1);

namespace SdFramework;

use SdFramework\Container\Container;
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
use Throwable;

class Application extends Container
{
    protected static ?Container $instance = null;
    private string $basePath;
    private Router $router;
    private array $coreProviders = [];
    private array $featureProviders = [];
    private array $loadedProviders = [];
    private bool $booted = false;
    private bool $isDebug;
    private Handler $errorHandler;
    private Environment $env;
    private EventDispatcher $events;
    private Config $config;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        self::$instance = $this;
        
        try {
            $this->bootstrap();
        } catch (Throwable $e) {
            $this->handleBootstrapError($e);
        }
    }

    private function bootstrap(): void
    {
        // Initialize environment
        $this->initializeEnvironment();
        
        // Initialize config
        $this->initializeConfig();
        
        // Initialize event dispatcher
        $this->events = new EventDispatcher($this);
        $this->instance(EventDispatcher::class, $this->events);
        
        // Dispatch booting event
        $this->events->dispatch(new ApplicationBooting($this));
        
        // Register base bindings
        $this->registerBaseBindings();
        $this->registerCoreProviders();
        $this->registerFeatureProviders();
        $this->bootProviders();
        
        // Initialize error handler after providers are registered
        $this->errorHandler = $this->make(Handler::class);
        $this->isDebug = $this->env->getBoolean('APP_DEBUG', false);
        
        // Dispatch booted event
        $this->events->dispatch(new ApplicationBooted($this));
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

    private function initializeConfig(): void
    {
        try {
            $this->config = new Config($this->basePath . '/config');
            $this->instance(Config::class, $this->config);
            $this->alias(Config::class, 'config');
        } catch (Throwable $e) {
            throw new ApplicationException(
                'Failed to initialize config: ' . $e->getMessage()
            );
        }
    }

    private function handleBootstrapError(Throwable $e): void
    {
        // Fallback error handling when the application fails to bootstrap
        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, sprintf(
                "Fatal Error: Could not bootstrap application: %s\n%s\n",
                $e->getMessage(),
                $e->getTraceAsString()
            ));
            exit(1);
        }

        http_response_code(500);
        if ($this->isDebug ?? true) {
            echo sprintf(
                '<h1>Fatal Error</h1><p>Could not bootstrap application: %s</p><pre>%s</pre>',
                htmlspecialchars($e->getMessage()),
                htmlspecialchars($e->getTraceAsString())
            );
        } else {
            echo '<h1>500 Internal Server Error</h1><p>The application could not be started.</p>';
        }
        exit(1);
    }

    private function registerBaseBindings(): void
    {
        try {
            // Register self
            
            $this->instance('app', $this);
            $this->instance(Container::class, $this);
            $this->instance(Application::class, $this);
            
            // Create and register router
            $this->singleton(Router::class, function ($app) {
                return new Router($app);
            });
            $this->router = $this->make(Router::class);
        } catch (Throwable $e) {
            throw new ContainerException(
                'Failed to register base bindings: ' . $e->getMessage()
            );
        }
    }

    private function registerCoreProviders(): void
    {
        try {
            // Register core service providers in correct order
            $coreProviders = config('app.core_providers', [
                \SdFramework\ServiceProvider\LogServiceProvider::class,
                \SdFramework\ServiceProvider\EventServiceProvider::class,
                \SdFramework\Support\ServiceProvider\HelperServiceProvider::class,
                \SdFramework\ServiceProvider\DatabaseServiceProvider::class,
                \SdFramework\Error\ServiceProvider\ErrorServiceProvider::class,
                
                // Add more core providers here
            ]);

            foreach ($coreProviders as $provider) {
                $this->registerServiceProvider($provider, $this->coreProviders);
            }
        } catch (Throwable $e) {
            throw new ApplicationException(
                'Failed to register core providers: ' . $e->getMessage()
            );
        }
    }

    private function registerFeatureProviders(): void
    {
        try {
            // Register feature service providers
            $featureProviders = config('app.providers', [
                \SdFramework\ServiceProvider\ModuleServiceProvider::class,
                \SdFramework\ServiceProvider\RouteServiceProvider::class,
                \SdFramework\ServiceProvider\ValidationServiceProvider::class,
            ]);

            foreach ($featureProviders as $provider) {
                $this->registerServiceProvider($provider, $this->featureProviders);
            }
        } catch (Throwable $e) {
            throw new ApplicationException(
                'Failed to register feature providers: ' . $e->getMessage()
            );
        }
    }

    public function registerServiceProvider(string $provider, array &$providers): void
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

            if ($this->booted && method_exists($providerInstance, 'boot')) {
                $providerInstance->boot();
            }

            $this->loadedProviders[$provider] = $providerInstance;
        } catch (Throwable $e) {
            throw new ApplicationException(
                "Failed to register service provider [{$provider}]: " . $e->getMessage()
            );
        }
    }

    private function bootProviders(): void
    {
        try {
            $this->booted = true;

            // Boot providers using stored instances
            foreach ($this->loadedProviders as $provider) {
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        } catch (Throwable $e) {
            throw new ApplicationException('Failed to boot providers: ' . $e->getMessage());
        }
    }

    public function handle(Request $request): Response
    {
        try {
            // Dispatch request handling event
            $this->events->dispatch(new RequestHandling($request));

            // Handle the request
            $response = $this->router->dispatch($request);

            // Dispatch request handled event
            $this->events->dispatch(new RequestHandled($request, $response));

            return $response;
        } catch (HttpException $e) {
            // Handle HTTP errors
            $this->events->dispatch(new RequestError($request, $e));
            throw $e;
        } catch (Throwable $e) {
            // Handle other errors
            $this->events->dispatch(new RequestError($request, $e));
            throw new HttpException($e->getMessage(),500, [] , $e);
        }
    }

    public function terminate(Request $request, Response $response): void
    {
        // Perform cleanup, logging, etc.
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function isDebug(): bool
    {
        return $this->isDebug;
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

    public function getConfig(): Config
    {
        return $this->config;
    }

    public static function getInstance(): ?Container
    {
        return static::$instance;
    }

    public static function setInstance(?Container $container = null): ?Container
    {
        return static::$instance = $container;
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this;
    }
}
