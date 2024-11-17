<?php

declare(strict_types=1);

namespace SdFramework\Database;

use SdFramework\Support\Helper\Implementations\ArrayHelper;
use Countable;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class Collection implements Countable, ArrayAccess, IteratorAggregate
{
    protected array $items = [];
    protected ?string $modelClass = null;

    public function __construct(array $items = [], ?string $modelClass = null)
    {
        $this->items = $items;
        $this->modelClass = $modelClass;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            if ($value instanceof Model) {
                return $value->toArray();
            }
            return $value;
        }, $this->items);
    }

    public function toArrayHelper(): ArrayHelper
    {
        return arr($this->toArray());
    }

    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->items));
    }

    public function filter(callable $callback): self
    {
        return new static(array_filter($this->items, $callback));
    }

    public function pluck(string $key): array
    {
        return $this->toArrayHelper()->pluck($key);
    }

    public function where(string $key, $value, string $operator = '='): self
    {
        return $this->filter(function ($item) use ($key, $value, $operator) {
            $itemValue = $item instanceof Model ? $item->$key : $item[$key];
            
            switch ($operator) {
                case '=':
                    return $itemValue === $value;
                case '!=':
                    return $itemValue !== $value;
                case '>':
                    return $itemValue > $value;
                case '<':
                    return $itemValue < $value;
                case '>=':
                    return $itemValue >= $value;
                case '<=':
                    return $itemValue <= $value;
                default:
                    return false;
            }
        });
    }

    public function first()
    {
        return $this->items[0] ?? null;
    }

    public function last()
    {
        return end($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
