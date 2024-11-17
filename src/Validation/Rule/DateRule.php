<?php

declare(strict_types=1);

namespace SdFramework\Validation\Rule;

class DateRule extends AbstractRule
{
    public function validate(mixed $value, array $parameters = [], array $data = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    public function getDefaultMessage(): string
    {
        return 'The :field must be a valid date.';
    }
}
