<?php

declare(strict_types=1);

namespace SdFramework\Database\Relations;

use SdFramework\Database\Model;
use SdFramework\Database\QueryBuilder;
use SdFramework\Database\Collection;

abstract class Relation
{
    protected Model $parent;
    protected string $related;
    protected string $foreignKey;
    protected string $localKey;
    protected ?QueryBuilder $query = null;

    public function __construct(Model $parent, string $related, string $foreignKey, string $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->initializeQuery();
    }

    abstract protected function initializeQuery(): void;

    protected function getResults(): mixed
    {
        return $this->query?->get() ?? new Collection();
    }

    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
}
