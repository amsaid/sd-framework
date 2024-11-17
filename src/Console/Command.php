<?php

declare(strict_types=1);

namespace SdFramework\Console;

abstract class Command
{
    protected string $name = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    abstract public function handle(array $arguments = [], array $options = []): int;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
