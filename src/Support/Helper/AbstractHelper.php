<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper;

abstract class AbstractHelper implements Helper
{
    protected string $name;
    protected string $description;

    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    abstract public function handle(mixed ...$args): mixed;
}
