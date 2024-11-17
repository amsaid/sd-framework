<?php

declare(strict_types=1);

namespace SdFramework\Event;

use Psr\EventDispatcher\StoppableEventInterface as PsrStoppableEventInterface;

/**
 * An Event whose processing may be interrupted when the event has been handled.
 */
interface StoppableEventInterface extends PsrStoppableEventInterface
{
}
