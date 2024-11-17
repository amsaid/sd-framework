<?php

declare(strict_types=1);

namespace SdFramework\Database;

abstract class Migration
{
    protected Connection $connection;

    public function __construct()
    {
        $this->connection = app(Connection::class);
    }

    abstract public function up(): void;
    
    abstract public function down(): void;

    protected function createTable(string $table, callable $callback): void
    {
        $schema = new Schema\Table($table);
        $callback($schema);
        
        $this->connection->statement($schema->toSql());
    }

    protected function dropTable(string $table): void
    {
        $this->connection->statement("DROP TABLE IF EXISTS `{$table}`");
    }

    protected function addColumn(string $table, string $column, string $type, array $options = []): void
    {
        $schema = new Schema\Column($column, $type, $options);
        $this->connection->statement(
            "ALTER TABLE `{$table}` ADD COLUMN " . $schema->toSql()
        );
    }

    protected function dropColumn(string $table, string $column): void
    {
        $this->connection->statement(
            "ALTER TABLE `{$table}` DROP COLUMN `{$column}`"
        );
    }

    protected function createIndex(string $table, string|array $columns, string $name = null, bool $unique = false): void
    {
        $columns = (array) $columns;
        $name = $name ?: $table . '_' . implode('_', $columns) . '_index';
        $type = $unique ? 'UNIQUE' : '';
        
        $this->connection->statement(
            "CREATE {$type} INDEX `{$name}` ON `{$table}` (`" . implode('`, `', $columns) . "`)"
        );
    }

    protected function dropIndex(string $table, string $name): void
    {
        $this->connection->statement(
            "DROP INDEX `{$name}` ON `{$table}`"
        );
    }
}
