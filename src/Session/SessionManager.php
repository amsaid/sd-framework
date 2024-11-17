<?php

namespace SdFramework\Session;

use SdFramework\Config\Config;
use SdFramework\Session\Handler\FileHandler;
use SdFramework\Session\Handler\RedisHandler;
use SessionHandlerInterface;

class SessionManager
{
    private Config $config;
    private ?SessionHandlerInterface $handler = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        $this->configure();
        
        return session_start();
    }

    protected function configure(): void
    {
        // Set session handler
        $handler = $this->getHandler();
        if ($handler) {
            session_set_save_handler($handler, true);
        }

        // Configure session cookie
        session_name($this->config->get('session.cookie', 'sd_session'));
        
        session_set_cookie_params([
            'lifetime' => $this->config->get('session.lifetime', 120) * 60,
            'path' => $this->config->get('session.path', '/'),
            'domain' => $this->config->get('session.domain'),
            'secure' => $this->config->get('session.secure', false),
            'httponly' => $this->config->get('session.http_only', true),
            'samesite' => $this->config->get('session.same_site', 'Lax')
        ]);
    }

    protected function getHandler(): ?SessionHandlerInterface
    {
        if ($this->handler !== null) {
            return $this->handler;
        }

        $driver = $this->config->get('session.driver', 'file');
        $config = $this->config->get("session.handlers.{$driver}");

        if (!$config) {
            throw new \RuntimeException("Session handler [{$driver}] is not configured.");
        }

        // Add lifetime to handler config
        $config['lifetime'] = $this->config->get('session.lifetime', 120);

        $this->handler = match($driver) {
            'file' => new FileHandler($config),
            'redis' => new RedisHandler($config),
            default => throw new \InvalidArgumentException("Session handler [{$driver}] is not supported.")
        };

        return $this->handler;
    }

    public function getId(): string
    {
        return session_id();
    }

    public function regenerate(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            return true;
        }

        return false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash'][$key] ?? $default;
    }

    public function clearFlash(): void
    {
        unset($_SESSION['_flash']);
    }
}
