<?php

declare(strict_types=1);

namespace SdFramework\Event\Events\Http;

use SdFramework\Event\Event;
use SdFramework\Http\Request;
use SdFramework\Http\Response;

/**
 * Request has been handled and response is ready.
 */
class RequestHandled extends Event
{
    public function __construct(
        private Request $request,
        private Response $response
    ) {
    }

    public function getName(): string
    {
        return 'request.handled';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
