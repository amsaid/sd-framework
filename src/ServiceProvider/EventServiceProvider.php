<?php

declare(strict_types=1);

namespace SdFramework\ServiceProvider;

use SdFramework\Event\EventDispatcher;
use SdFramework\Event\Subscribers\LogSubscriber;
use Psr\Log\LoggerInterface;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new EventDispatcher($app);
        });

        // Register LogSubscriber
        $this->app->singleton(LogSubscriber::class, function ($app) {
            return new LogSubscriber($app->make(LoggerInterface::class));
        });
    }

    public function boot(): void
    {
        /** @var EventDispatcher $events */
        $events = $this->app->make(EventDispatcher::class);
        
        // Register default subscribers
        $events->addSubscriber($this->app->make(LogSubscriber::class));
    }
}
