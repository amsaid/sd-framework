<?php

declare(strict_types=1);

namespace SdFramework\Event\Subscribers;

use Psr\Log\LoggerInterface;
use SdFramework\Event\EventSubscriberInterface;
use SdFramework\Event\Events\Application\ApplicationBooted;
use SdFramework\Event\Events\Application\ApplicationBooting;
use SdFramework\Event\Events\Http\RequestError;
use SdFramework\Event\Events\Http\RequestHandled;
use SdFramework\Event\Events\Http\RequestHandling;

class LogSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'application.booting' => 'onApplicationBooting',
            'application.booted' => 'onApplicationBooted',
            'request.handling' => 'onRequestHandling',
            'request.handled' => 'onRequestHandled',
            'request.error' => 'onRequestError'
        ];
    }

    public function onApplicationBooting(ApplicationBooting $event): void
    {
        $this->logger->info('Application is booting');
    }

    public function onApplicationBooted(ApplicationBooted $event): void
    {
        $this->logger->info('Application has booted');
    }

    public function onRequestHandling(RequestHandling $event): void
    {
        $request = $event->getRequest();
        $this->logger->info('Handling request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }

    public function onRequestHandled(RequestHandled $event): void
    {
        $response = $event->getResponse();
        $this->logger->info('Request handled', [
            'status' => $response->getStatusCode()
        ]);
    }

    public function onRequestError(RequestError $event): void
    {
        $error = $event->getError();
        $this->logger->error('Request error', [
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine()
        ]);
    }
}
