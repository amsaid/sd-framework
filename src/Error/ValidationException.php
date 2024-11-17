<?php

declare(strict_types=1);

namespace SdFramework\Error;

class ValidationException extends HttpException
{
    protected array $errors;

    public function __construct(
        array $errors,
        string $message = 'The given data was invalid.',
        int $code = 422,
        array $headers = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $headers, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
