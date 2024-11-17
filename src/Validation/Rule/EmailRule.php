<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class EmailRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true; // Empty is valid unless combined with required rule
        }
        
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must be a valid email address.';
    }
}
