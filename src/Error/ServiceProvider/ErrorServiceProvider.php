<?php

declare(strict_types=1);

namespace SdFramework\Error\ServiceProvider;

use SdFramework\ServiceProvider\ServiceProvider;
use SdFramework\Error\Handler;
use Psr\Log\LoggerInterface;

class ErrorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Handler::class, function ($app) {
            $config = $app->getConfig();
            
            // Create logger for error handling
            $logger = null;
            if ($app->has(LoggerInterface::class)) {
                $logger = $app->make(LoggerInterface::class);
                $channel = $config->get('app.error.log.channel', 'error');
                $level = $config->get('app.error.log.level', 'error');
                if (method_exists($logger, 'channel')) {
                    $logger = $logger->channel($channel)->level($level);
                }
            }
            
            // Create error handler instance
            $handler = new Handler(
                $config->get('app.error.debug', false),
                $logger,
                $app->getBasePath() . '/resources/views/' . 
                    $config->get('app.error.template_dir', 'error')
            );

            // Configure silent exceptions
            $silent = $config->get('app.error.silent', []);
            $handler->setSilentExceptions($silent);

            return $handler;
        });

        // Register error handler immediately
        $this->app->make(Handler::class)->register();
    }

    public function boot(): void
    {
        // No boot actions needed
    }
}
