<?php

declare(strict_types=1);

namespace SdFramework\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    private string $path;
    private string $defaultChannel;
    private array $channels = [];
    private string $minimumLevel;
    private array $levelPriorities = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    ];

    public function __construct(string $path, string $defaultChannel = 'app', string $minimumLevel = LogLevel::DEBUG)
    {
        $this->path = rtrim($path, '/');
        $this->defaultChannel = $defaultChannel;
        $this->minimumLevel = $minimumLevel;
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function level(string $level): self
    {
        if (!isset($this->levelPriorities[$level])) {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }

        $this->minimumLevel = $level;
        return $this;
    }

    public function channel(string $channel): self
    {
        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = new self($this->path . '/' . $channel, $channel, $this->minimumLevel);
        }

        return $this->channels[$channel];
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        // Check if we should log this level
        if (!$this->shouldLog($level)) {
            return;
        }

        $logFile = $this->path . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        
        $message = $this->interpolate($message, $context);
        $logLine = sprintf(
            '[%s] %s.%s: %s%s',
            $timestamp,
            strtoupper($level),
            $this->defaultChannel,
            $message,
            PHP_EOL
        );

        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    private function shouldLog(string $level): bool
    {
        return $this->levelPriorities[$level] >= $this->levelPriorities[$this->minimumLevel];
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replace);
    }
}
