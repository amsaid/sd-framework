<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Module\ValidationModule;
use SdFramework\Validation\RuleRegistry;

class ValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the RuleRegistry as a singleton
        $this->app->getContainer()->singleton(RuleRegistry::class);
        
        // Register the ValidationModule
        $moduleManager = $this->app->getContainer()->get('modules');
        $moduleManager->register(ValidationModule::class);
    }

    public function boot(): void
    {
        // Boot validation module if not already booted
        $moduleManager = $this->app->getContainer()->get('modules');
        $module = $moduleManager->getModule('validation');
        
        if ($module && !$module->isBooted()) {
            $module->boot();
        }
    }
}
