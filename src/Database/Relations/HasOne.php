<?php

declare(strict_types=1);

namespace SdFramework\Database\Relations;

class HasOne extends Relation
{
    protected function initializeQuery(): void
    {
        $this->query = $this->related::query()
            ->where($this->foreignKey, '=', $this->parent->{$this->localKey});
    }

    public function getResults(): mixed
    {
        return $this->query?->first();
    }
}
