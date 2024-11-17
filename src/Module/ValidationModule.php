<?php

declare(strict_types=1);

namespace SdFramework\Module;

use SdFramework\Validation\RuleRegistry;
use SdFramework\Validation\Rule;

class ValidationModule extends Module
{
    private RuleRegistry $ruleRegistry;

    public function getName(): string
    {
        return 'validation';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Provides extensible validation capabilities with custom rule support';
    }

    public function boot(): void
    {
        $this->ruleRegistry = new RuleRegistry();
        
        // Register core validation rules
        $this->registerCoreRules();
        
        // Register custom rules from configuration
        $this->registerCustomRules();
        
        // Make validator available in container
        $this->container->singleton(RuleRegistry::class, fn() => $this->ruleRegistry);
    }

    private function registerCoreRules(): void
    {
        $coreRules = [
            'required' => new Rule\RequiredRule(),
            'email' => new Rule\EmailRule(),
            'min' => new Rule\MinRule(),
            'max' => new Rule\MaxRule(),
            'numeric' => new Rule\NumericRule(),
            'alpha' => new Rule\AlphaRule(),
            'alphanumeric' => new Rule\AlphanumericRule(),
            'url' => new Rule\UrlRule(),
            'date' => new Rule\DateRule(),
            'matches' => new Rule\MatchesRule(),
        ];

        foreach ($coreRules as $name => $rule) {
            $this->ruleRegistry->register($name, $rule);
        }
    }

    private function registerCustomRules(): void
    {
        $customRules = $this->getConfig('rules', []);
        
        foreach ($customRules as $name => $ruleClass) {
            if (class_exists($ruleClass) && is_subclass_of($ruleClass, Rule::class)) {
                $this->ruleRegistry->register($name, new $ruleClass());
            }
        }
    }

    protected function onEnable(): void
    {
        // Load validation messages from database
        $messages = $this->loadMessagesFromDatabase();
        $this->registerConfig('messages', $messages);
    }

    private function loadMessagesFromDatabase(): array
    {
        return $this->db->table('core_config')
            ->where('path', 'LIKE', 'validation.messages.%')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = str_replace('validation.messages.', '', $row->path);
                return [$key => $row->value];
            })
            ->toArray();
    }
}
