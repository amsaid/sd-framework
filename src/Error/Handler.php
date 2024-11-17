<?php

declare(strict_types=1);

namespace SdFramework\Error;

use ErrorException;
use Throwable;
use SdFramework\Http\Response;
use SdFramework\Http\Request;
use Psr\Log\LoggerInterface;

class Handler
{
    protected bool $debug;
    protected array $silentExceptions = [];
    protected ?LoggerInterface $logger;
    protected string $templatePath;

    public function __construct(
        bool $debug = false,
        ?LoggerInterface $logger = null,
        string $templatePath = ''
    ) {
        $this->debug = $debug;
        $this->logger = $logger;
        $this->templatePath = $templatePath;
    }

    public function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0
    ): void {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public function handleException(Throwable $e): void
    {
        $this->log($e);
        $this->render($e)->send();
    }

    public function handleShutdown(): void
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    protected function isFatal(int $type): bool
    {
        return in_array($type, [
            E_COMPILE_ERROR,
            E_CORE_ERROR,
            E_ERROR,
            E_PARSE
        ]);
    }

    public function log(Throwable $e): void
    {
        if ($this->shouldBeSilent($e)) {
            return;
        }

        if ($this->logger) {
            $context = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];

            if ($e instanceof HttpException) {
                $context['status'] = $e->getStatusCode();
                $context['headers'] = $e->getHeaders();
            }

            $this->logger->error($e->getMessage(), $context);
        }
    }

    public function render(Throwable $e): Response
    {
        $statusCode = $this->getStatusCode($e);
        $headers = $e instanceof HttpException ? $e->getHeaders() : [];

        if ($this->debug) {
            return $this->renderDebug($e, $statusCode, $headers);
        }

        return $this->renderTemplate($e, $statusCode, $headers);
    }

    protected function renderDebug(Throwable $e, int $statusCode, array $headers): Response
    {
        $content = [
            'error' => [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ]
        ];

        $headers['Content-Type'] = 'application/json';
        return new Response(json_encode($content, JSON_PRETTY_PRINT), $statusCode, $headers);
    }

    protected function renderTemplate(Throwable $e, int $statusCode, array $headers): Response
    {
        $template = $this->findTemplate($statusCode);
        
        if ($template && file_exists($template)) {
            $content = $this->loadTemplate($template, [
                'exception' => $e,
                'status' => $statusCode,
                'message' => $e->getMessage(),
            ]);
        } else {
            $content = $this->getDefaultMessage($statusCode);
        }

        $headers['Content-Type'] = 'text/html';
        return new Response($content, $statusCode, $headers);
    }

    protected function findTemplate(int $statusCode): string
    {
        $templates = [
            $this->templatePath . "/{$statusCode}.php",
            $this->templatePath . '/error.php',
        ];

        foreach ($templates as $template) {
            if (file_exists($template)) {
                return $template;
            }
        }

        return '';
    }

    protected function loadTemplate(string $template, array $data): string
    {
        extract($data);
        ob_start();
        include $template;
        return ob_get_clean();
    }

    protected function getDefaultMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        return $messages[$statusCode] ?? 'An error occurred';
    }

    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        return match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof NotFoundException => 404,
            $e instanceof MethodNotAllowedException => 405,
            $e instanceof TokenMismatchException => 419,
            $e instanceof TooManyRequestsException => 429,
            default => 500,
        };
    }

    public function setSilentExceptions(array $exceptions): void
    {
        $this->silentExceptions = $exceptions;
    }

    protected function shouldBeSilent(Throwable $e): bool
    {
        foreach ($this->silentExceptions as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
