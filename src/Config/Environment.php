<?php

declare(strict_types=1);

namespace SdFramework\Config;

use RuntimeException;

class Environment
{
    private array $variables = [];
    private bool $loaded = false;

    /**
     * Load environment variables from a file.
     *
     * @throws RuntimeException If the file cannot be read
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Environment file not found: $path");
        }

        if (!is_readable($path)) {
            throw new RuntimeException("Environment file is not readable: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException("Failed to read environment file: $path");
        }
        
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!empty($name)) {
                $value = $this->parseValue($value);
                $value = $this->interpolateValue($value);
                $this->set($name, $value);
            }
        }

        $this->loaded = true;
    }

    /**
     * Parse the value of an environment variable.
     */
    private function parseValue(string $value): string
    {
        $value = trim($value);
        
        if ($value === 'true') return 'true';
        if ($value === 'false') return 'false';
        if ($value === 'null') return '';
        
        // Remove quotes if present
        if (preg_match('/^".*"$/', $value)) {
            return trim($value, '"');
        }
        
        if (preg_match("/^'.*'$/", $value)) {
            return trim($value, "'");
        }

        // Handle command substitution for APP_KEY
        if (str_starts_with($value, '$(') && str_ends_with($value, ')')) {
            $command = substr($value, 2, -1);
            $output = shell_exec($command);
            return $output ? trim($output) : '';
        }

        return $value;
    }

    /**
     * Interpolate variables in a value.
     */
    private function interpolateValue(string $value): string
    {
        return preg_replace_callback('/\${([a-zA-Z][a-zA-Z0-9_]*)}/', function ($matches) {
            $varName = $matches[1];
            return $this->get($varName, '');
        }, $value);
    }

    /**
     * Get an environment variable.
     *
     * @param string $key The variable name
     * @param mixed $default Default value if the variable is not set
     * @return mixed The value of the environment variable
     */
    public function get(string $key, $default = null)
    {
        // Check local variables first
        if (isset($this->variables[$key])) {
            return $this->variables[$key];
        }

        // Then check system environment
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        // Finally check $_ENV and $_SERVER
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return $default;
    }

    /**
     * Set an environment variable.
     */
    public function set(string $key, string $value): void
    {
        if (empty($key)) {
            throw new RuntimeException('Environment variable key cannot be empty');
        }

        $this->variables[$key] = $value;
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /**
     * Check if an environment variable exists.
     */
    public function has(string $key): bool
    {
        return isset($this->variables[$key]) || 
               getenv($key) !== false || 
               isset($_ENV[$key]) || 
               isset($_SERVER[$key]);
    }

    /**
     * Get all environment variables.
     */
    public function all(): array
    {
        return $this->variables;
    }

    /**
     * Check if the environment file has been loaded.
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Get a boolean value from an environment variable.
     */
    public function getBoolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get an integer value from an environment variable.
     */
    public function getInteger(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }
}
