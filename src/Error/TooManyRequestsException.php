<?php

declare(strict_types=1);

namespace SdFramework\Error;

class TooManyRequestsException extends HttpException
{
    protected int $retryAfter;

    public function __construct(
        int $retryAfter,
        string $message = 'Too Many Requests',
        array $headers = []
    ) {
        $headers['Retry-After'] = (string) $retryAfter;
        parent::__construct($message, 429, $headers);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
