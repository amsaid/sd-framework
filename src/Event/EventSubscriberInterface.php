<?php

declare(strict_types=1);

namespace SdFramework\Event;

/**
 * Interface for event subscribers.
 */
interface EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     * 
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name and priority
     *  * An array of arrays composed of the method names and priorities
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array<string, string|array>
     */
    public static function getSubscribedEvents(): array;
}
