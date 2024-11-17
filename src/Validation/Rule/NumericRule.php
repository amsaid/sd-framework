<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class NumericRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        return is_numeric($value);
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must be a numeric value.';
    }
}
