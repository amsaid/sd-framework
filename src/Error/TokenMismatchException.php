<?php

declare(strict_types=1);

namespace SdFramework\Error;

class TokenMismatchException extends HttpException
{
    public function __construct(
        string $message = 'CSRF token mismatch',
        array $headers = []
    ) {
        parent::__construct($message, 419, $headers);
    }
}
