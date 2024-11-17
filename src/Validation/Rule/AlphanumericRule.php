<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class AlphanumericRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^[\pL\pN\s]+$/u', $value) === 1;
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must only contain letters and numbers.';
    }
}
