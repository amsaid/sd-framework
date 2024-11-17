<?php

declare(strict_types=1);

use SdFramework\Application;
use SdFramework\Console\Application as ConsoleApplication;
use SdFramework\Container\Container;
use SdFramework\Support\Helper\HelperRegistry;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract Optional abstract type to resolve
     * @param array $parameters Optional parameters to pass to the resolved type
     * @return mixed|Application|Container The container instance or resolved type
     */
    function app(string $abstract = null, array $parameters = []): mixed
    {
        $app = Application::getInstance() ?? ConsoleApplication::getInstance();

        if ($abstract === null) {
            return $app;
        }

        return $app->make($abstract, $parameters);
    }
}

if (!function_exists('helpers')) {
    /**
     * Get the helper registry instance or a specific helper.
     *
     * @param string|null $name Helper name
     * @return mixed HelperRegistry or specific helper instance
     */
    function helpers(?string $name = null): mixed
    {
        $registry = app(HelperRegistry::class);
        
        if (is_null($name)) {
            return $registry;
        }
        
        return $registry->get($name);
    }
}

if (!function_exists('arr')) {
    /**
     * Array helper function.
     *
     * @param array|null $array Input array
     * @return mixed ArrayHelper instance or result
     */
    function arr(?array $array = null): mixed
    {
        $helper = helpers('array');
        
        if (is_null($array)) {
            return $helper;
        }
        
        return $helper->handle($array);
    }
}

if (!function_exists('str')) {
    /**
     * String helper function.
     *
     * @param string|null $string Input string
     * @return mixed StringHelper instance or result
     */
    function str(?string $string = null): mixed
    {
        $helper = helpers('str');
        
        if (is_null($string)) {
            return $helper;
        }
        
        return $helper->handle($string);
    }
}

if (!function_exists('collect')) {
    /**
     * Create a new collection from the given value.
     *
     * @param mixed $value
     * @return \SdFramework\Support\Collection
     */
    function collect(mixed $value = []): \SdFramework\Support\Collection
    {
        return new \SdFramework\Support\Collection($value);
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value.
     *
     * @param string|null $key Configuration key using dot notation
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = app('config');
        
        if (is_null($key)) {
            return $config;
        }
        
        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable value.
     *
     * @param string $key Environment variable key
     * @param mixed $default Default value if key not found
     * @return mixed Environment value
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
        
        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the value or execute the closure if it's a closure.
     *
     * @param mixed $value
     * @return mixed
     */
    function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given value, optionally passed through a callback.
     *
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function with(mixed $value, ?callable $callback = null): mixed
    {
        if (is_null($callback)) {
            return $value;
        }
        
        return $callback($value);
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the application base path.
     *
     * @param string $path Path to append to base path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return app()->getBasePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path Path to append to config path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return base_path('config') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the var directory path.
     *
     * @param string $path Path to append to var path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return base_path('var') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the public path.
     *
     * @param string $path Path to append to public path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the passed variables with improved formatting.
     *
     * @param  mixed  ...$args
     * @return void
     */
    function dump(...$args): void
    {
        $styles = '
            <style>
                .debug-dump { 
                    background: #f8f9fa; 
                    border: 1px solid #dee2e6; 
                    border-radius: 4px; 
                    padding: 15px; 
                    margin: 10px 0; 
                    font-family: monospace; 
                    font-size: 14px; 
                    line-height: 1.5;
                }
                .debug-dump .type { color: #0d6efd; }
                .debug-dump .key { color: #dc3545; }
                .debug-dump .value { color: #198754; }
                .debug-dump .bracket { color: #6c757d; }
            </style>
        ';
        static $hasStyles = false;
        
        if (!$hasStyles) {
            echo $styles;
            $hasStyles = true;
        }

        foreach ($args as $x) {
            echo "\n<div class='debug-dump'>";
            if (is_null($x)) {
                echo "<span class='type'>null</span>";
            } elseif (is_bool($x)) {
                echo "<span class='type'>bool</span>(<span class='value'>" . ($x ? 'true' : 'false') . "</span>)";
            } elseif (is_string($x)) {
                echo "<span class='type'>string</span>(<span class='value'>" . htmlspecialchars($x) . "</span>)";
            } elseif (is_int($x) || is_float($x)) {
                echo "<span class='type'>" . gettype($x) . "</span>(<span class='value'>" . $x . "</span>)";
            } elseif (is_array($x)) {
                echo "<span class='type'>array</span> <span class='bracket'>[</span>\n";
                foreach ($x as $key => $value) {
                    echo "  <span class='key'>" . htmlspecialchars($key) . "</span> => ";
                    dump($value);
                }
                echo "<span class='bracket'>]</span>";
            } elseif (is_object($x)) {
                $class = get_class($x);
                echo "<span class='type'>" . htmlspecialchars($class) . "</span> <span class='bracket'>{</span>\n";
                $reflect = new ReflectionObject($x);
                $props = $reflect->getProperties();
                foreach ($props as $prop) {
                    $prop->setAccessible(true);
                    $value = $prop->getValue($x);
                    echo "  <span class='key'>" . $prop->getName() . "</span> => ";
                    dump($value);
                }
                echo "<span class='bracket'>}</span>";
            } else {
                echo "<span class='type'>" . gettype($x) . "</span>(<span class='value'>" . htmlspecialchars((string)$x) . "</span>)";
            }
            echo "</div>\n";
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  ...$args
     * @return never
     */
    function dd(...$args): never
    {
        dump(...$args);
        exit(1);
    }
}
