<?php

declare(strict_types=1);

namespace SdFramework\Validation;

class RuleRegistry
{
    private array $rules = [];

    public function register(string $name, Rule $rule): void
    {
        $this->rules[$name] = $rule;
    }

    public function getRule(string $name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    public function hasRule(string $name): bool
    {
        return isset($this->rules[$name]);
    }

    public function unregister(string $name): void
    {
        unset($this->rules[$name]);
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
