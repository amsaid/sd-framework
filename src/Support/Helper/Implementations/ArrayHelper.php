<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper\Implementations;

use SdFramework\Support\Helper\AbstractHelper;

class ArrayHelper extends AbstractHelper
{
    public function __construct()
    {
        parent::__construct(
            'array',
            'Helper for advanced array operations'
        );
    }

    public function handle(mixed ...$args): mixed
    {
        // Default behavior when called directly
        if (empty($args)) {
            return [];
        }

        return $args[0] ?? null;
    }

    public function get(array $array, string|int $key, mixed $default = null): mixed
    {
        return $array[$key] ?? $default;
    }

    public function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge(
                    $results,
                    $this->dot($value, $prepend . $key . '.')
                );
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    public function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    public function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    public function pluck(array $array, string $key, ?string $keyBy = null): array
    {
        $results = [];

        foreach ($array as $item) {
            if (!is_array($item) && !is_object($item)) {
                continue;
            }

            $itemArray = (array) $item;
            if (!isset($itemArray[$key])) {
                continue;
            }

            if ($keyBy !== null) {
                $keyByValue = $itemArray[$keyBy] ?? null;
                if ($keyByValue !== null) {
                    $results[$keyByValue] = $itemArray[$key];
                    continue;
                }
            }

            $results[] = $itemArray[$key];
        }

        return $results;
    }

    public function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    public function groupBy(array $array, string $key): array
    {
        $results = [];

        foreach ($array as $item) {
            if (!is_array($item) && !is_object($item)) {
                continue;
            }

            $itemArray = (array) $item;
            if (!isset($itemArray[$key])) {
                continue;
            }

            $groupKey = $itemArray[$key];
            if (!isset($results[$groupKey])) {
                $results[$groupKey] = [];
            }

            $results[$groupKey][] = $item;
        }

        return $results;
    }
}
