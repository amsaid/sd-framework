<?php

declare(strict_types=1);

namespace SdFramework\Validation;

interface Rule
{
    /**
     * Validate the given value.
     *
     * @param mixed $value The value to validate
     * @param array $parameters Additional parameters for the rule
     * @param array $data All data being validated
     * @return bool
     */
    public function validate(mixed $value, array $parameters = [], array $data = []): bool;

    /**
     * Get the validation error message.
     *
     * @param string $field The field being validated
     * @param array $parameters Additional parameters for the rule
     * @return string
     */
    public function getMessage(string $field, array $parameters = []): string;

    /**
     * Get the default error message.
     *
     * @return string
     */
    public function getDefaultMessage(): string;
}
