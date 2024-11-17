<?php

declare(strict_types=1);

namespace SdFramework\Error;

/**
 * Exception thrown when there is an error in the application's core functionality.
 */
class ApplicationException extends HttpException
{
    public function __construct(string $message = "", int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, [],  $previous );
    }
}
