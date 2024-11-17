<?php

declare(strict_types=1);

namespace SdFramework\Http;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $server;
    private array $headers;
    private array $cookies;
    private array $files;
    private ?string $content;

    public function __construct(
        string $method,
        string $path,
        array $query = [],
        array $post = [],
        array $server = [],
        array $headers = [],
        array $cookies = [],
        array $files = [],
        ?string $content = null
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->post = $post;
        $this->server = $server;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->content = $content;
    }

    public static function capture(): self
    {
        return static::fromGlobals();
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Get request body content for non-POST requests
        $content = null;
        if ($method !== 'GET' && $method !== 'POST') {
            $content = file_get_contents('php://input');
        }

        return new self(
            $method,
            $path,
            $_GET,
            $_POST,
            $_SERVER,
            getallheaders(),
            $_COOKIE,
            $_FILES,
            $content
        );
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getQueryParam(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function getPost(): array
    {
        return $this->post;
    }

    public function getPostParam(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function getServer(): array
    {
        return $this->server;
    }

    public function getServerParam(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name, $default = null)
    {
        $name = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return $default;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getCookie(string $name, $default = null)
    {
        return $this->cookies[$name] ?? $default;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    public function isHead(): bool
    {
        return $this->isMethod('HEAD');
    }

    public function isXmlHttpRequest(): bool
    {
        return 'XMLHttpRequest' === $this->getHeader('X-Requested-With');
    }

    public function isSecure(): bool
    {
        $https = $this->getServerParam('HTTPS');
        return !empty($https) && strtolower($https) !== 'off';
    }
}
