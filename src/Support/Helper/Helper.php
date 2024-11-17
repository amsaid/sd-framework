<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper;

interface Helper
{
    /**
     * Handle the helper functionality.
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function handle(mixed ...$args): mixed;

    /**
     * Get the helper's name.
     */
    public function getName(): string;

    /**
     * Get the helper's description.
     */
    public function getDescription(): string;
}
