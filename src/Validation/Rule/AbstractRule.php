<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

use SdFramework\Validation\Rule;

abstract class AbstractRule implements Rule
{
    protected string $message;

    public function getMessage(string $field, array $parameters = []): string
    {
        $message = $this->message ?? $this->getDefaultMessage();
        $message = str_replace(':field', $field, $message);
        
        if (!empty($parameters)) {
            $message = str_replace(':param', $parameters[0], $message);
        }
        
        return $message;
    }

    abstract public function validate(mixed $value, array $parameters = [], array $data = []): bool;
    
    abstract public function getDefaultMessage(): string;
}
