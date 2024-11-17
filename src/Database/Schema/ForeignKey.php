<?php

declare(strict_types=1);

namespace SdFramework\Database\Schema;

class ForeignKey
{
    private string $tableName;
    private string $column;
    private string $referencedTable;
    private string $referencedColumn;
    private string $onDelete = 'RESTRICT';
    private string $onUpdate = 'RESTRICT';

    public function __construct(string $tableName, string $column)
    {
        $this->tableName = $tableName;
        $this->column = $column;
    }

    public function references(string $column): self
    {
        $this->referencedColumn = $column;
        return $this;
    }

    public function on(string $table): self
    {
        $this->referencedTable = $table;
        return $this;
    }

    public function onDelete(string $action): self
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    public function toSql(): string
    {
        return sprintf(
            'CONSTRAINT `%s_%s_foreign` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s',
            $this->tableName,
            $this->column,
            $this->column,
            $this->referencedTable,
            $this->referencedColumn,
            $this->onDelete,
            $this->onUpdate
        );
    }
}
