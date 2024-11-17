<?php

declare(strict_types=1);

namespace SdFramework\Event;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base event class that all events should extend.
 * 
 * Implements StoppableEventInterface from PSR-14 for compatibility.
 */
abstract class Event implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    /**
     * Get the event name.
     */
    abstract public function getName(): string;

    /**
     * Stop event propagation.
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
