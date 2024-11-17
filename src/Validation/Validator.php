<?php

declare(strict_types=1);

namespace SdFramework\Validation;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private RuleRegistry $ruleRegistry;

    public function __construct(array $data, array $rules, RuleRegistry $ruleRegistry)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->ruleRegistry = $ruleRegistry;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($rules as $ruleString) {
                $params = [];
                $ruleName = $ruleString;
                
                if (str_contains($ruleString, ':')) {
                    [$ruleName, $param] = explode(':', $ruleString, 2);
                    $params = explode(',', $param);
                }

                $rule = $this->ruleRegistry->getRule($ruleName);
                
                if ($rule) {
                    $value = $this->data[$field] ?? null;
                    
                    if (!$rule->validate($value, $params, $this->data)) {
                        $this->addError($field, $rule->getMessage($field, $params));
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public static function make(array $data, array $rules, RuleRegistry $ruleRegistry): self
    {
        return new self($data, $rules, $ruleRegistry);
    }
}
