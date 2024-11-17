<?php

declare(strict_types=1);

namespace SdFramework\Error;

class NotFoundException extends HttpException
{
    public function __construct(
        string $message = 'Not Found',
        array $headers = []
    ) {
        parent::__construct($message, 404, $headers);
    }
}
