<?php

declare(strict_types=1);

namespace SdFramework\Event;

use Closure;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use SdFramework\Container\Container;

/**
 * Event dispatcher responsible for registering and dispatching events.
 * 
 * Implements PSR-14 EventDispatcherInterface and ListenerProviderInterface.
 */
class EventDispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    private array $listeners = [];
    private array $sorted = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add an event listener.
     *
     * @param string $eventName Event name
     * @param callable|array|string $listener Listener callback or class method
     * @param int $priority Higher priorities execute first (default: 0)
     */
    public function addListener(string $eventName, callable|array|string $listener, int $priority = 0): void
    {
        $this->listeners[$eventName][$priority][] = $this->resolveListener($listener);
        unset($this->sorted[$eventName]);
    }

    /**
     * Remove an event listener.
     */
    public function removeListener(string $eventName, callable|array|string $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        $listener = $this->resolveListener($listener);

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            $key = array_search($listener, $listeners, true);
            if ($key !== false) {
                unset($this->listeners[$eventName][$priority][$key], $this->sorted[$eventName]);
            }
        }
    }

    /**
     * Add an event subscriber.
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
            } elseif (is_array($params)) {
                foreach ($params as $listener) {
                    $priority = $listener[1] ?? 0;
                    $this->addListener($eventName, [$subscriber, $listener[0]], $priority);
                }
            }
        }
    }

    /**
     * Remove an event subscriber.
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->removeListener($eventName, [$subscriber, $listener[0]]);
                }
            } else {
                $this->removeListener($eventName, [$subscriber, is_string($params) ? $params : $params[0]]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event): object
    {
        if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
            return $event;
        }

        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }

        return $event;
    }

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = $event instanceof Event ? $event->getName() : get_class($event);

        if (!isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return $this->sorted[$eventName] ?? [];
    }

    /**
     * Get all listeners for an event.
     */
    public function getListeners(string $eventName = null): array
    {
        if ($eventName !== null) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }

            return $this->sorted[$eventName];
        }

        foreach ($this->listeners as $eventName => $eventListeners) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }
        }

        return array_filter($this->sorted);
    }

    /**
     * Check if an event has listeners.
     */
    public function hasListeners(string $eventName = null): bool
    {
        return (bool) count($this->getListeners($eventName));
    }

    /**
     * Sort listeners by priority.
     */
    private function sortListeners(string $eventName): void
    {
        $this->sorted[$eventName] = [];

        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = array_merge(...$this->listeners[$eventName]);
        }
    }

    /**
     * Resolve a listener to a callable.
     */
    private function resolveListener(callable|array|string $listener): callable
    {
        if (is_string($listener) && str_contains($listener, '::')) {
            $listener = explode('::', $listener);
        }

        if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            $listener[0] = $this->container->make($listener[0]);
        }

        if (!is_callable($listener)) {
            throw new InvalidArgumentException('Event listener must be callable');
        }

        return $listener;
    }
}
