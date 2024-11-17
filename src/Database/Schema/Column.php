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

    public function toSql(): string
    {
        $parts = ["`{$this->name}`"];
        
        // Type
        $type = strtoupper($this->type);
        if (isset($this->options['length'])) {
            $type .= "({$this->options['length']})";
        }
        $parts[] = $type;
        
        // Unsigned
        if ($this->unsigned) {
            $parts[] = 'UNSIGNED';
        }
        
        // Nullable
        $parts[] = $this->nullable ? 'NULL' : 'NOT NULL';
        
        // Default
        if ($this->hasDefault) {
            if ($this->default === null) {
                $parts[] = 'DEFAULT NULL';
            } else {
                $parts[] = 'DEFAULT ' . $this->formatDefaultValue($this->default);
            }
        }
        
        // Auto Increment
        if ($this->autoIncrement) {
            $parts[] = 'AUTO_INCREMENT';
        }
        
        // Primary Key
        if ($this->primary) {
            $parts[] = 'PRIMARY KEY';
        }
        
        return implode(' ', $parts);
    }

    private function formatDefaultValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        return "'" . addslashes($value) . "'";
    }
}
