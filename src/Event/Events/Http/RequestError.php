<?php

declare(strict_types=1);

namespace SdFramework\Event\Events\Http;

use SdFramework\Event\Event;
use SdFramework\Http\Request;
use Throwable;

/**
 * An error occurred during request handling.
 */
class RequestError extends Event
{
    public function __construct(
        private Request $request,
        private Throwable $error
    ) {
    }

    public function getName(): string
    {
        return 'request.error';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getError(): Throwable
    {
        return $this->error;
    }
}
