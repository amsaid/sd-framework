<?php

declare(strict_types=1);

namespace SdFramework\Error;

class AuthenticationException extends HttpException
{
    public function __construct(
        string $message = 'Unauthenticated.',
        array $headers = []
    ) {
        parent::__construct($message, 401, $headers);
    }
}
