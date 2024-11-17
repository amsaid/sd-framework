<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class UrlRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must be a valid URL.';
    }
}
