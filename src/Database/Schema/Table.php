<?php

declare(strict_types=1);

namespace SdFramework\Database\Schema;

class Table
{
    private string $name;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private ?string $engine = null;
    private ?string $charset = null;
    private ?string $collation = null;
    private ?string $comment = null;
    private bool $temporary = false;
    private bool $ifNotExists = false;
    private ?string $rowFormat = null;
    private ?string $storageEngine = null;

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

    public function uuid(string $name = 'id'): Column
    {
        return $this->string($name, 36)->primary();
    }

    public function string(string $name, int $length = 255): Column
    {
        return $this->column($name, 'varchar', ['length' => $length]);
    }

    public function text(string $name): Column
    {
        return $this->column($name, 'text');
    }

    public function mediumText(string $name): Column
    {
        return $this->column($name, 'mediumtext');
    }

    public function longText(string $name): Column
    {
        return $this->column($name, 'longtext');
    }

    public function json(string $name): Column
    {
        return $this->column($name, 'longtext', ['is_json' => true]);
    }

    public function jsonb(string $name): Column
    {
        // MariaDB doesn't have native JSONB, fallback to JSON (longtext)
        return $this->json($name);
    }

    public function integer(string $name): Column
    {
        return $this->column($name, 'int');
    }

    public function tinyInteger(string $name): Column
    {
        return $this->column($name, 'tinyint');
    }

    public function smallInteger(string $name): Column
    {
        return $this->column($name, 'smallint');
    }

    public function mediumInteger(string $name): Column
    {
        return $this->column($name, 'mediumint');
    }

    public function bigInteger(string $name): Column
    {
        return $this->column($name, 'bigint');
    }

    public function boolean(string $name): Column
    {
        return $this->tinyInteger($name)->length(1);
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): Column
    {
        return $this->column($name, 'decimal', [
            'precision' => $precision,
            'scale' => $scale
        ]);
    }

    public function float(string $name, int $precision = 8, int $scale = 2): Column
    {
        return $this->column($name, 'float', [
            'precision' => $precision,
            'scale' => $scale
        ]);
    }

    public function double(string $name, int $precision = 8, int $scale = 2): Column
    {
        return $this->column($name, 'double', [
            'precision' => $precision,
            'scale' => $scale
        ]);
    }

    public function enum(string $name, array $values): Column
    {
        return $this->column($name, 'enum', ['values' => $values]);
    }

    public function set(string $name, array $values): Column
    {
        return $this->column($name, 'set', ['values' => $values]);
    }

    public function binary(string $name, int $length = 255): Column
    {
        return $this->column($name, 'binary', ['length' => $length]);
    }

    public function varbinary(string $name, int $length = 255): Column
    {
        return $this->column($name, 'varbinary', ['length' => $length]);
    }

    public function blob(string $name): Column
    {
        return $this->column($name, 'blob');
    }

    public function date(string $name): Column
    {
        return $this->column($name, 'date');
    }

    public function datetime(string $name): Column
    {
        return $this->column($name, 'datetime');
    }

    public function time(string $name): Column
    {
        return $this->column($name, 'time');
    }

    public function timestamp(string $name): Column
    {
        return $this->column($name, 'timestamp');
    }

    public function year(string $name): Column
    {
        return $this->column($name, 'year');
    }

    public function timestamps(bool $precision = false): void
    {
        $type = $precision ? 'datetime(6)' : 'datetime';
        $this->column('created_at', $type)->nullable();
        $this->column('updated_at', $type)->nullable();
    }

    public function softDeletes(bool $precision = false): void
    {
        $type = $precision ? 'datetime(6)' : 'datetime';
        $this->column('deleted_at', $type)->nullable();
    }

    public function temporary(): self
    {
        $this->temporary = true;
        return $this;
    }

    public function ifNotExists(): self
    {
        $this->ifNotExists = true;
        return $this;
    }

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function rowFormat(string $format): self
    {
        $this->rowFormat = strtoupper($format);
        return $this;
    }

    public function storageEngine(string $engine): self
    {
        $this->storageEngine = strtoupper($engine);
        return $this;
    }

    public function column(string $name, string $type, array $options = []): Column
    {
        $column = new Column($name, $type, $options);
        $this->columns[] = $column;
        return $column;
    }

    public function index(string|array $columns, string $name = null, ?string $algorithm = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_index';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'index',
            'algorithm' => $algorithm
        ];
    }

    public function unique(string|array $columns, string $name = null, ?string $algorithm = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_unique';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'unique',
            'algorithm' => $algorithm
        ];
    }

    public function spatial(string|array $columns, string $name = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_spatial';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'spatial'
        ];
    }

    public function fulltext(string|array $columns, string $name = null): void
    {
        $columns = (array) $columns;
        $name = $name ?: $this->name . '_' . implode('_', $columns) . '_fulltext';
        $this->indexes[] = [
            'columns' => $columns,
            'name' => $name,
            'type' => 'fulltext'
        ];
    }

    public function foreign(string $column): ForeignKey
    {
        $foreignKey = new ForeignKey($this->name, $column);
        $this->foreignKeys[] = $foreignKey;
        return $foreignKey;
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
        $parts = [];
        
        // Create statement
        $parts[] = $this->temporary ? 'CREATE TEMPORARY TABLE' : 'CREATE TABLE';
        
        // If not exists
        if ($this->ifNotExists) {
            $parts[] = 'IF NOT EXISTS';
        }
        
        $parts[] = "`{$this->name}`";
        $parts[] = "(";
        
        // Columns
        $columnDefinitions = [];
        $primaryKey = null;
        
        foreach ($this->columns as $column) {
            $columnDefinitions[] = "  " . $column->toSql();
            if ($column->isPrimary()) {
                $primaryKey = $column->getName();
            }
            if ($column->isUnique()) {
                $this->unique($column->getName());
            }
        }
        
        // Primary Key
        if ($primaryKey) {
            $columnDefinitions[] = "  PRIMARY KEY (`{$primaryKey}`)";
        }
        
        // Indexes
        foreach ($this->indexes as $index) {
            $indexColumns = implode('`, `', $index['columns']);
            $indexType = strtoupper($index['type']);
            $indexDef = "  {$indexType} `{$index['name']}` (`{$indexColumns}`)";
            
            // Add index algorithm if specified
            if (isset($index['algorithm']) && in_array(strtoupper($index['algorithm']), ['BTREE', 'HASH'])) {
                $indexDef .= " USING " . strtoupper($index['algorithm']);
            }
            
            $columnDefinitions[] = $indexDef;
        }
        
        // Foreign Keys
        foreach ($this->foreignKeys as $foreignKey) {
            $columnDefinitions[] = "  " . $foreignKey->toSql();
        }
        
        $parts[] = implode(",\n", $columnDefinitions);
        $parts[] = ")";
        
        // Table options
        $options = [];
        
        if ($this->engine || $this->storageEngine) {
            $options[] = "ENGINE = " . ($this->storageEngine ?? $this->engine);
        }
        
        if ($this->charset) {
            $options[] = "DEFAULT CHARSET = {$this->charset}";
        }
        
        if ($this->collation) {
            $options[] = "COLLATE = {$this->collation}";
        }
        
        if ($this->rowFormat) {
            $options[] = "ROW_FORMAT = {$this->rowFormat}";
        }
        
        if ($this->comment) {
            $options[] = "COMMENT = '" . addslashes($this->comment) . "'";
        }
        
        if (!empty($options)) {
            $parts[] = implode(' ', $options);
        }
        
        return implode(' ', $parts) . ";";
    }
}
