<?php

declare(strict_types=1);

namespace SdFramework\Database\Schema;

class Column
{
    private string $name;
    private string $type;
    private array $options;
    private bool $nullable = false;
    private mixed $default = null;
    private bool $hasDefault = false;
    private bool $unsigned = false;
    private bool $autoIncrement = false;
    private bool $primary = false;
    private bool $unique = false;
    private ?int $length = null;
    private ?string $after = null;
    private bool $first = false;
    private ?string $charset = null;
    private ?string $collation = null;
    private ?string $comment = null;
    private bool $invisible = false;
    private ?string $check = null;

    public function __construct(string $name, string $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        
        if (isset($options['unsigned'])) {
            $this->unsigned = $options['unsigned'];
        }
        if (isset($options['autoIncrement'])) {
            $this->autoIncrement = $options['autoIncrement'];
        }
        if (isset($options['primary'])) {
            $this->primary = $options['primary'];
        }
        if (isset($options['length'])) {
            $this->length = $options['length'];
        }
    }

    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    public function unsigned(bool $value = true): self
    {
        $this->unsigned = $value;
        return $this;
    }

    public function autoIncrement(bool $value = true): self
    {
        $this->autoIncrement = $value;
        return $this;
    }

    public function primary(bool $value = true): self
    {
        $this->primary = $value;
        return $this;
    }

    public function unique(bool $value = true): self
    {
        $this->unique = $value;
        return $this;
    }

    public function length(int $value): self
    {
        $this->length = $value;
        return $this;
    }

    public function after(string $column): self
    {
        $this->after = $column;
        return $this;
    }

    public function first(): self
    {
        $this->first = true;
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

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function invisible(bool $value = true): self
    {
        $this->invisible = $value;
        return $this;
    }

    public function check(string $expression): self
    {
        $this->check = $expression;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    private function formatDefaultValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value === null) {
            return 'NULL';
        }
        if (is_array($value) || is_object($value)) {
            return "'" . addslashes(json_encode($value)) . "'";
        }
        return (string) $value;
    }

    public function toSql(): string
    {
        $parts = ["`{$this->name}`"];
        
        // Type
        $type = strtoupper($this->type);
        
        // Handle special types
        if ($this->type === 'enum' || $this->type === 'set') {
            $values = array_map(fn($v) => "'" . addslashes($v) . "'", $this->options['values']);
            $type .= '(' . implode(',', $values) . ')';
        } elseif (in_array($this->type, ['decimal', 'float', 'double']) && isset($this->options['precision'], $this->options['scale'])) {
            $type .= "({$this->options['precision']},{$this->options['scale']})";
        } elseif ($this->length !== null) {
            $type .= "({$this->length})";
        } elseif (isset($this->options['length'])) {
            $type .= "({$this->options['length']})";
        }
        
        // Handle JSON type (MariaDB uses CHECK constraint)
        if (isset($this->options['is_json']) && $this->options['is_json']) {
            $this->check("JSON_VALID(`{$this->name}`)");
        }
        
        $parts[] = $type;
        
        // Unsigned
        if ($this->unsigned && in_array($this->type, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double'])) {
            $parts[] = 'UNSIGNED';
        }
        
        // Character set and collation
        if ($this->charset) {
            $parts[] = "CHARACTER SET {$this->charset}";
        }
        if ($this->collation) {
            $parts[] = "COLLATE {$this->collation}";
        }
        
        // Nullable
        $parts[] = $this->nullable ? 'NULL' : 'NOT NULL';
        
        // Default
        if ($this->hasDefault) {
            $parts[] = 'DEFAULT ' . $this->formatDefaultValue($this->default);
        }
        
        // Auto Increment
        if ($this->autoIncrement) {
            $parts[] = 'AUTO_INCREMENT';
        }
        
        // Invisible (MariaDB 10.3+)
        if ($this->invisible) {
            $parts[] = 'INVISIBLE';
        }
        
        // Check constraint
        if ($this->check) {
            $parts[] = "CHECK ({$this->check})";
        }
        
        // Comment
        if ($this->comment) {
            $parts[] = "COMMENT '" . addslashes($this->comment) . "'";
        }
        
        // Column position
        if ($this->after) {
            $parts[] = "AFTER `{$this->after}`";
        } elseif ($this->first) {
            $parts[] = 'FIRST';
        }
        
        return implode(' ', $parts);
    }
}
