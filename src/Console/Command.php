<?php

declare(strict_types=1);

namespace SdFramework\Console;

abstract class Command
{
    protected Application $app;
    protected string $name = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    abstract public function handle(array $arguments = [], array $options = []): int;

    public function setApplication(Application $app): void
    {
        $this->app = $app;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function execute(array $argv): int
    {
        $arguments = array_slice($argv, 2);
        $options = $this->parseOptions($arguments);
        return $this->handle($arguments, $options);
    }

    protected function parseOptions(array &$arguments): array
    {
        $options = [];
        
        foreach ($arguments as $i => $arg) {
            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                if (str_contains($option, '=')) {
                    list($key, $value) = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;
                }
                unset($arguments[$i]);
            }
        }
        
        $arguments = array_values($arguments);
        return $options;
    }

    protected function output(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    protected function error(string $message): void
    {
        fwrite(STDERR, "Error: " . $message . PHP_EOL);
    }

    protected function confirm(string $question): bool
    {
        $this->output($question . ' (yes/no) [no]: ');
        $handle = fopen('php://stdin', 'r');
        $answer = trim(fgets($handle));
        fclose($handle);

        return strtolower($answer) === 'yes' || strtolower($answer) === 'y';
    }

    protected function ask(string $question, ?string $default = null): string
    {
        $defaultText = $default !== null ? " [$default]" : '';
        $this->output($question . $defaultText . ': ');
        
        $handle = fopen('php://stdin', 'r');
        $answer = trim(fgets($handle));
        fclose($handle);

        return $answer !== '' ? $answer : ($default ?? '');
    }
}
