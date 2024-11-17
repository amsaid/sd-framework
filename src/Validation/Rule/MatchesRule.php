<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class MatchesRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (empty($parameters)) {
            return false;
        }

        $otherField = $parameters[0];
        return isset($data[$otherField]) && $value === $data[$otherField];
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must match :param.';
    }
}
