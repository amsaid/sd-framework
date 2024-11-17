<?php

declare(strict_types=1);

namespace SdFramework\Event\Events\Http;

use SdFramework\Event\Event;
use SdFramework\Http\Request;

/**
 * Request is starting to be handled.
 */
class RequestHandling extends Event
{
    public function __construct(
        private Request $request
    ) {
    }

    public function getName(): string
    {
        return 'request.handling';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
