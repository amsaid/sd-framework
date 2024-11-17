<?php

declare(strict_types=1);

namespace SdFramework\Database;

use SdFramework\Error\HttpException;

class DatabaseException extends HttpException
{
    public function __construct(string $message = "", int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, [], $previous);
    }
}
