<?php

declare(strict_types=1);

namespace SdFramework\Event\Events\Application;

use SdFramework\Application;
use SdFramework\Event\Event;

/**
 * Application has finished booting, all service providers are registered.
 */
class ApplicationBooted extends Event
{
    public function __construct(private Application $app)
    {
    }

    public function getName(): string
    {
        return 'application.booted';
    }

    public function getApplication(): Application
    {
        return $this->app;
    }
}
