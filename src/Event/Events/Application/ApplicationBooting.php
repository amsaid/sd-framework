<?php

declare(strict_types=1);

namespace SdFramework\Event\Events\Application;

use SdFramework\Application;
use SdFramework\Event\Event;

/**
 * Application is booting, before any service providers are registered.
 */
class ApplicationBooting extends Event
{
    public function __construct(private Application $app)
    {
    }

    public function getName(): string
    {
        return 'application.booting';
    }

    public function getApplication(): Application
    {
        return $this->app;
    }
}
