<?php

declare(strict_types=1);

namespace SdFramework\Error;

class HttpException extends \Exception
{
    protected array $headers = [];

    public function __construct(
        string $message = '',
        int $code = 500,
        array $headers = [],
        ?\Throwable $previous = null
        
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}
