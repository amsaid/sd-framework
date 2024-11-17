<?php

declare(strict_types=1);

namespace SdFramework\Error;

class MethodNotAllowedException extends HttpException
{
    protected array $allowedMethods;

    public function __construct(
        array $allowedMethods,
        string $message = 'Method Not Allowed',
        array $headers = []
    ) {
        $headers['Allow'] = implode(', ', $allowedMethods);
        parent::__construct($message, 405, $headers);
        $this->allowedMethods = $allowedMethods;
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
