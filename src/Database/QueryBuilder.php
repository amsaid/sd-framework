<?php

declare(strict_types=1);

namespace SdFramework\Database;

use PDO;
use PDOStatement;

class QueryBuilder
{
    private PDO $pdo;
    private string $table = '';
    private array $columns = ['*'];
    private array $where = [];
    private array $params = [];
    private string $orderBy = '';
    private ?int $limit = null;
    private ?int $offset = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->where[] = "$column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY $column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $query = $this->buildSelectQuery();
        $stmt = $this->executeQuery($query, $this->params);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit = 1;
        $query = $this->buildSelectQuery();
        $stmt = $this->executeQuery($query, $this->params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    public function exists(): bool
    {
        $this->columns = ['1'];
        $query = $this->buildSelectQuery();
        $stmt = $this->executeQuery($query, $this->params);
        return $stmt->fetch() !== false;
    }

    public function count(): int
    {
        $this->columns = ['COUNT(*) as count'];
        $query = $this->buildSelectQuery();
        $stmt = $this->executeQuery($query, $this->params);
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }

    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->executeQuery($query, $values);
        return $stmt->rowCount() > 0;
    }

    public function update(array $data): bool
    {
        $set = [];
        $values = [];

        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $values[] = $value;
        }

        $query = sprintf(
            'UPDATE %s SET %s %s',
            $this->table,
            implode(', ', $set),
            $this->buildWhereClause()
        );

        $stmt = $this->executeQuery($query, array_merge($values, $this->params));
        return $stmt->rowCount() > 0;
    }

    public function delete(): bool
    {
        $query = sprintf(
            'DELETE FROM %s %s',
            $this->table,
            $this->buildWhereClause()
        );

        $stmt = $this->executeQuery($query, $this->params);
        return $stmt->rowCount() > 0;
    }

    private function buildSelectQuery(): string
    {
        return sprintf(
            'SELECT %s FROM %s %s %s %s %s',
            implode(', ', $this->columns),
            $this->table,
            $this->buildWhereClause(),
            $this->orderBy,
            $this->limit !== null ? "LIMIT $this->limit" : '',
            $this->offset !== null ? "OFFSET $this->offset" : ''
        );
    }

    private function buildWhereClause(): string
    {
        return !empty($this->where) ? 'WHERE ' . implode(' AND ', $this->where) : '';
    }

    private function executeQuery(string $query, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}
