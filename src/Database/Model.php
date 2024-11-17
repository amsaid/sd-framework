<?php

declare(strict_types=1);

namespace SdFramework\Database;

use SdFramework\Database\Relations\{HasOne, HasMany, BelongsTo};
use SdFramework\Database\Collection;

abstract class Model
{
    protected static string $table;
    protected static array $fillable = [];
    protected array $attributes = [];
    protected array $relations = [];
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
        if (array_key_exists($name, $this->relations)) {
            return $this->relations[$name];
        }

        if (method_exists($this, $name)) {
            return $this->relations[$name] = $this->$name()->getResults();
        }

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

    public static function find($id): ?static
    {
        $result = static::query()->where('id', '=', $id)->first();
        return $result ? new static((array)$result) : null;
    }

    public static function all(): Collection
    {
        $results = static::query()->get();
        return new Collection(
            array_map(fn($data) => new static((array)$data), $results),
            static::class
        );
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

    protected function hasOne(string $related, string $foreignKey, string $localKey = 'id'): HasOne
    {
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    protected function hasMany(string $related, string $foreignKey, string $localKey = 'id'): HasMany
    {
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    protected function belongsTo(string $related, string $foreignKey, string $localKey = 'id'): BelongsTo
    {
        return new BelongsTo($this, $related, $foreignKey, $localKey);
    }
}
