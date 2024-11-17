<?php

declare(strict_types=1);

namespace SdFramework\Database\Relations;

class HasMany extends Relation
{
    protected function initializeQuery(): void
    {
        $this->query = $this->related::query()
            ->where($this->foreignKey, '=', $this->parent->{$this->localKey});
    }
}
