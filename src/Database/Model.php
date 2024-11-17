<?php

declare(strict_types=1);

namespace SdFramework\Database;

abstract class Model
{
    protected static string $table;
    protected static array $fillable = [];
    protected array $attributes = [];
    protected static ?Connection $connection = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        if (in_array($name, static::$fillable)) {
            $this->attributes[$name] = $value;
        }
    }

    public static function setConnection(Connection $connection): void
    {
        static::$connection = $connection;
    }

    protected static function getConnection(): Connection
    {
        if (static::$connection === null) {
            throw new \RuntimeException('Database connection not set');
        }
        return static::$connection;
    }

    public static function query(): QueryBuilder
    {
        return static::getConnection()->table(static::$table);
    }

    public static function find($id)
    {
        $result = static::query()->where('id', '=', $id)->first();
        return $result ? new static($result) : null;
    }

    public static function all(): array
    {
        $results = static::query()->get();
        return array_map(fn($data) => new static($data), $results);
    }

    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public function save(): bool
    {
        if (isset($this->attributes['id'])) {
            return static::query()
                ->where('id', '=', $this->attributes['id'])
                ->update($this->attributes);
        }

        return static::query()->insert($this->attributes);
    }

    public function delete(): bool
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }

        return static::query()
            ->where('id', '=', $this->attributes['id'])
            ->delete();
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
