<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class RequiredRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        return $value !== null && $value !== '';
    }

    public function getDefaultMessage(): string
    {
        return 'The :field field is required.';
    }
}
