<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

use SdFramework\Validation\Rule;

class MinRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (empty($parameters)) {
            return true;
        }

        $min = (int) $parameters[0];

        if (is_numeric($value)) {
            return (float) $value >= $min;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    public function getMessage(string $field, array $parameters = []): string
    {
        $min = $parameters[0] ?? 'unknown';
        return str_replace(
            [':field', ':min'],
            [$field, $min],
            $this->getDefaultMessage()
        );
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must be at least :min.';
    }
}
