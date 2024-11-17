<?php

declare(strict_types=1);

namespace SdFramework\Database\Schema;

class Table
{
    private string $name;
    private array $columns = [];
    private array $indexes = [];
    private ?string $engine = null;
    private ?string $charset = null;
    private ?string $collation = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function id(string $name = 'id'): Column
    {
        return $this->column($name, 'bigint', [
            'unsigned' => true,
            'autoIncrement' => true,
            'primary' => true,
        ]);
    }

    public function string(string $name, int $length = 255): Column
    {
        return $this->column($name, 'varchar', ['length' => $length]);
    }

    public function text(string $name): Column
    {
        return $this->column($name, 'text');
    }

    public function integer(string $name): Column
    {
        return $this->column($name, 'int');
    }

    public function bigInteger(string $name): Column
    {
        return $this->column($name, 'bigint');
    }

    public function boolean(string $name): Column
    {
        return $this->column($name, 'tinyint', ['length' => 1]);
    }

    public function datetime(string $name): Column
    {
        return $this->column($name, 'datetime');
    }

    public function timestamp(string $name): Column
    {
        return $this->column($name, 'timestamp');
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    public function softDeletes(): void
    {
        $this->timestamp('deleted_at')->nullable();
    }

    public function column(string $name, string $type, array $options = []): Column
    {
        $column = new Column($name, $type, $options);
        $this->columns[] = $column;
        return $column;
    }

    public function index(string|array $columns, string $name = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_index';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'index'
        ];
    }

    public function unique(string|array $columns, string $name = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_unique';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'unique'
        ];
    }

    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    public function toSql(): string
    {
        $sql = ["CREATE TABLE `{$this->name}` ("];
        
        // Columns
        $columnDefinitions = [];
        foreach ($this->columns as $column) {
            $columnDefinitions[] = "  " . $column->toSql();
        }
        
        // Indexes
        foreach ($this->indexes as $index) {
            $type = $index['type'] === 'unique' ? 'UNIQUE KEY' : 'KEY';
            $columnDefinitions[] = "  {$type} `{$index['name']}` (`" . implode('`, `', $index['columns']) . "`)";
        }
        
        $sql[] = implode(",\n", $columnDefinitions);
        $sql[] = ")";
        
        // Table options
        $options = [];
        if ($this->engine) {
            $options[] = "ENGINE={$this->engine}";
        }
        if ($this->charset) {
            $options[] = "DEFAULT CHARSET={$this->charset}";
        }
        if ($this->collation) {
            $options[] = "COLLATE={$this->collation}";
        }
        
        if (!empty($options)) {
            $sql[] = implode(' ', $options);
        }
        
        return implode("\n", $sql);
    }
}
