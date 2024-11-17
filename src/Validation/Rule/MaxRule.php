<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class MaxRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (empty($parameters)) {
            return true;
        }

        $max = (int) $parameters[0];

        if (is_numeric($value)) {
            return (float) $value <= $max;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must not exceed :param.';
    }
}
