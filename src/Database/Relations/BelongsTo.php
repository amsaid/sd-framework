<?php

declare(strict_types=1);

namespace SdFramework\Database\Relations;

class BelongsTo extends Relation
{
    protected function initializeQuery(): void
    {
        $this->query = $this->related::query()
            ->where($this->localKey, '=', $this->parent->{$this->foreignKey});
    }

    public function getResults(): mixed
    {
        return $this->query?->first();
    }
}
