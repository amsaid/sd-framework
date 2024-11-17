<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper\Implementations;

use SdFramework\Support\Helper\AbstractHelper;

class StringHelper extends AbstractHelper
{
    protected string $value = '';

    public function __construct(string $value = '')
    {
        parent::__construct(
            'str',
            'Helper for string manipulation operations'
        );
        $this->value = $value;
    }

    public function handle(mixed ...$args): mixed
    {
        if (empty($args)) {
            return $this->value;
        }

        return new static((string) ($args[0] ?? ''));
    }

    public function camel(): self
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $this->value));
        $studlyWords = array_map('ucfirst', $words);
        
        return new static(lcfirst(implode('', $studlyWords)));
    }

    public function snake(string $delimiter = '_'): self
    {
        $value = $this->value;
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }
        
        return new static($value);
    }

    public function studly(): self
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $this->value));
        
        return new static(implode('', array_map('ucfirst', $words)));
    }

    public function kebab(): self
    {
        return $this->snake('-');
    }

    public function slug(string $separator = '-'): self
    {
        $value = $this->value;
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';
        $value = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $value);
        
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $value = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($value));
        
        // Replace all separator characters and whitespace by a single separator
        $value = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $value);
        
        return new static(trim($value, $separator));
    }

    public static function random(int $length = 16): self
    {
        $string = '';
        
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        
        return new static($string);
    }

    public function truncate(int $length, string $end = '...'): self
    {
        $value = $this->value;
        if (mb_strlen($value) <= $length) {
            return new static($value);
        }

        return new static(rtrim(mb_substr($value, 0, $length - mb_strlen($end))) . $end);
    }

    public function contains(string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($this->value, $needle) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function startsWith(string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_starts_with($this->value, $needle)) {
                return true;
            }
        }
        
        return false;
    }

    public function endsWith(string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_ends_with($this->value, $needle)) {
                return true;
            }
        }
        
        return false;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function length(): int
    {
        return mb_strlen($this->value);
    }
}
