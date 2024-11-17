<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper\Implementations;

use SdFramework\Support\Helper\AbstractHelper;

class StringHelper extends AbstractHelper
{
    public function __construct()
    {
        parent::__construct(
            'str',
            'Helper for string manipulation operations'
        );
    }

    public function handle(mixed ...$args): mixed
    {
        if (empty($args)) {
            return '';
        }

        return (string) ($args[0] ?? '');
    }

    public function camel(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        $studlyWords = array_map('ucfirst', $words);
        
        return lcfirst(implode('', $studlyWords));
    }

    public function snake(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }
        
        return $value;
    }

    public function studly(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        
        return implode('', array_map('ucfirst', $words));
    }

    public function kebab(string $value): string
    {
        return $this->snake($value, '-');
    }

    public function slug(string $value, string $separator = '-'): string
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';
        $value = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $value);
        
        // Remove all characters that are not the separator, letters, numbers, or whitespace
        $value = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($value));
        
        // Replace all separator characters and whitespace by a single separator
        $value = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $value);
        
        return trim($value, $separator);
    }

    public function random(int $length = 16): string
    {
        $string = '';
        
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        
        return $string;
    }

    public function truncate(string $value, int $length, string $end = '...'): string
    {
        if (mb_strlen($value) <= $length) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $length - mb_strlen($end))) . $end;
    }

    public function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }
        
        return false;
    }

    public function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }
        
        return false;
    }
}
