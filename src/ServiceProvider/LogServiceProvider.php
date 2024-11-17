<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SdFramework\Log\Logger;

class LogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoggerInterface::class, function ($container) {
            $env = $container->environment();
            
            $logPath = $env->get('LOG_PATH', 'var/logs');
            if (!str_starts_with($logPath, '/')) {
                $logPath = $container->getBasePath() . '/' . $logPath;
            }
            
            $channel = $env->get('LOG_CHANNEL', 'app');
            $level = $env->get('LOG_LEVEL', LogLevel::DEBUG);
            
            return new Logger($logPath, $channel, $level);
        });
    }
}
