<?php

declare(strict_types=1);

namespace SdFramework\Support\Helper\Implementations;

use SdFramework\Support\Helper\AbstractHelper;

class ArrayHelper extends AbstractHelper
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        parent::__construct(
            'array',
            'Helper for advanced array operations'
        );
        $this->items = $items;
    }

    public function handle(mixed ...$args): mixed
    {
        if (empty($args)) {
            return $this->items;
        }

        return new static($args[0] ?? []);
    }

    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function dot(string $prepend = ''): self
    {
        $results = [];

        foreach ($this->items as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge(
                    $results,
                    (new static($value))->dot($prepend . $key . '.')->toArray()
                );
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return new static($results);
    }

    public function except(array $keys): self
    {
        return new static(array_diff_key($this->items, array_flip($keys)));
    }

    public function only(array $keys): self
    {
        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

    public function pluck(string $key, ?string $keyBy = null): self
    {
        $results = [];

        foreach ($this->items as $item) {
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

        return new static($results);
    }

    public function where(callable $callback): self
    {
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function groupBy(string $key): self
    {
        $results = [];

        foreach ($this->items as $item) {
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

        return new static($results);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }
}
