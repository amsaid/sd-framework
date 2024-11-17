<?php

declare(strict_types=1);

namespace SdFramework\Database;

use PDO;
use PDOException;
use PDOStatement;
use SdFramework\Database\Schema;

class QueryBuilder
{
    private PDO $pdo;
    private Connection $connection;
    private string $table = '';
    private array $columns = ['*'];
    private array $where = [];
    private array $params = [];
    private string $orderBy = '';
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $modelClass = null;
    private ?string $sql = null;
    private array $joins = [];
    private array $having = [];
    private array $groupBy = [];
    private array $unions = [];

    public function __construct(Connection $connection, ?string $sql = null, array $params = [])
    {
        $this->connection = $connection;
        $this->pdo = $connection->getPdo();
        $this->sql = $sql;
        $this->params = $params;
    }

    public function setModel(string $modelClass): self
    {
        $this->modelClass = $modelClass;
        return $this;
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

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->where[] = [$column, $operator, $value];
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->where[] = ['OR', $column, $operator, $value];
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = rtrim(str_repeat('?,', count($values)), ',');
        $this->where[] = [$column, 'IN', "($placeholders)"];
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function whereNotIn(string $column, array $values): self
    {
        $placeholders = rtrim(str_repeat('?,', count($values)), ',');
        $this->where[] = [$column, 'NOT IN', "($placeholders)"];
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->where[] = [$column, 'IS', 'NULL'];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->where[] = [$column, 'IS NOT', 'NULL'];
        return $this;
    }

    public function whereBetween(string $column, mixed $start, mixed $end): self
    {
        $this->where[] = [$column, 'BETWEEN', [$start, $end]];
        return $this;
    }

    public function whereNotBetween(string $column, mixed $start, mixed $end): self
    {
        $this->where[] = [$column, 'NOT BETWEEN', [$start, $end]];
        return $this;
    }

    public function whereLike(string $column, string $pattern): self
    {
        $this->where[] = [$column, 'LIKE', $pattern];
        return $this;
    }

    public function whereNotLike(string $column, string $pattern): self
    {
        $this->where[] = [$column, 'NOT LIKE', $pattern];
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function groupBy(string|array $columns): self
    {
        $this->groupBy = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function having(string $column, string $operator, mixed $value): self
    {
        $this->having[] = [$column, $operator, $value];
        return $this;
    }

    public function union(QueryBuilder $query): self
    {
        $this->unions[] = ['type' => 'UNION', 'query' => $query];
        return $this;
    }

    public function unionAll(QueryBuilder $query): self
    {
        $this->unions[] = ['type' => 'UNION ALL', 'query' => $query];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = " ORDER BY {$column} " . strtoupper($direction);
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

    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = rtrim(str_repeat('?,', count($values)), ',');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            '`' . implode('`, `', $columns) . '`',
            $placeholders
        );

        return $this->connection->statement($sql, $values);
    }

    public function insertGetId(array $data): string
    {
        $this->insert($data);
        return $this->connection->lastInsertId();
    }

    public function update(array $data): bool
    {
        $set = [];
        $values = [];

        foreach ($data as $column => $value) {
            $set[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s',
            $this->table,
            implode(', ', $set)
        );

        if (!empty($this->where)) {
            $sql .= $this->buildWhereClause();
            $values = array_merge($values, $this->getWhereParams());
        }

        return $this->connection->statement($sql, $values);
    }

    public function delete(): bool
    {
        $sql = sprintf('DELETE FROM %s', $this->table);

        if (!empty($this->where)) {
            $sql .= $this->buildWhereClause();
            return $this->connection->statement($sql, $this->getWhereParams());
        }

        return $this->connection->statement($sql);
    }

    public function truncate(): bool
    {
        return $this->connection->statement("TRUNCATE TABLE {$this->table}");
    }

    public function get(): Collection
    {
        $query = $this->sql ?? $this->buildSelectQuery();
        $stmt = $this->executeQuery($query, $this->getParams());
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->modelClass) {
            $results = array_map(
                fn($data) => new $this->modelClass($data),
                $results
            );
        }

        return new Collection($results, $this->modelClass);
    }

    public function first(): ?object
    {
        $this->limit(1);
        $results = $this->get();
        return $results->first();
    }

    public function value(string $column)
    {
        $result = $this->first();
        return $result ? $result->{$column} : null;
    }

    public function count(): int
    {
        return (int) $this->aggregate('COUNT', '*');
    }

    public function max(string $column): mixed
    {
        return $this->aggregate('MAX', $column);
    }

    public function min(string $column): mixed
    {
        return $this->aggregate('MIN', $column);
    }

    public function sum(string $column): mixed
    {
        return $this->aggregate('SUM', $column);
    }

    public function avg(string $column): mixed
    {
        return $this->aggregate('AVG', $column);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    public function pluck(string $column, ?string $key = null): array
    {
        $this->columns = $key ? [$column, $key] : [$column];
        $results = $this->get();
        
        if ($key) {
            return array_column($results->toArray(), $column, $key);
        }
        
        return array_column($results->toArray(), $column);
    }

    public function chunk(int $size, callable $callback): bool
    {
        $page = 1;

        do {
            $results = $this->forPage($page, $size)->get();
            $countResults = $results->count();

            if ($countResults === 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            $page++;
        } while ($countResults === $size);

        return true;
    }

    public function forPage(int $page, int $perPage): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    public function tableExists(string $table = ''): bool
    {
        $targetTable = $table ?: $this->table;
        
        $query = "SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            AND table_name = ?";
            
        $stmt = $this->executeQuery($query, [$targetTable]);
        return (bool) $stmt->fetchColumn();
    }

    public function createTable(string $table, callable $callback): void
    {
        $schema = new Schema\Table($table);
        $callback($schema);

        $query = $schema->toSql();
        $this->connection->statement($query);
    }

    private function aggregate(string $function, string $column): mixed
    {
        $previousColumns = $this->columns;
        $previousGroupBy = $this->groupBy;

        $this->columns = ["{$function}({$column}) as aggregate"];
        $this->groupBy = [];

        $stmt = $this->executeQuery($this->buildSelectQuery(), $this->getParams());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->columns = $previousColumns;
        $this->groupBy = $previousGroupBy;

        return $result['aggregate'] ?? null;
    }

    private function buildSelectQuery(): string
    {
        $query = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $this->columns),
            $this->table
        );

        if (!empty($this->joins)) {
            $query .= $this->buildJoinClauses();
        }

        if (!empty($this->where)) {
            $query .= $this->buildWhereClause();
        }

        if (!empty($this->groupBy)) {
            $query .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $query .= $this->buildHavingClause();
        }

        if (!empty($this->unions)) {
            $query .= $this->buildUnionClauses();
        }

        if (!empty($this->orderBy)) {
            $query .= $this->orderBy;
        }

        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";

            if ($this->offset !== null) {
                $query .= " OFFSET {$this->offset}";
            }
        }

        return $query;
    }

    private function buildJoinClauses(): string
    {
        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    private function buildWhereClause(): string
    {
        $conditions = [];
        $firstWhere = true;

        foreach ($this->where as $condition) {
            if (isset($condition[3])) { // orWhere clause
                $conditions[] = ($firstWhere ? 'WHERE' : 'OR') . " `{$condition[1]}` {$condition[2]} ?";
            } else {
                $conditions[] = ($firstWhere ? 'WHERE' : 'AND') . " `{$condition[0]}` {$condition[1]} ?";
            }
            $firstWhere = false;
        }

        return ' ' . implode(' ', $conditions);
    }

    private function buildHavingClause(): string
    {
        $conditions = [];
        foreach ($this->having as $condition) {
            $conditions[] = "{$condition[0]} {$condition[1]} ?";
        }
        return ' HAVING ' . implode(' AND ', $conditions);
    }

    private function buildUnionClauses(): string
    {
        $sql = '';
        foreach ($this->unions as $union) {
            $sql .= " {$union['type']} " . $union['query']->buildSelectQuery();
        }
        return $sql;
    }

    private function executeQuery(string $query, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new DatabaseException(
                "Failed to execute query: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    private function getParams(): array
    {
        if ($this->sql !== null) {
            return $this->params;
        }

        $params = $this->getWhereParams();
        
        foreach ($this->having as $condition) {
            $params[] = $condition[2];
        }

        foreach ($this->unions as $union) {
            $params = array_merge($params, $union['query']->getParams());
        }

        return $params;
    }

    private function getWhereParams(): array
    {
        $params = [];
        foreach ($this->where as $condition) {
            if (isset($condition[3])) { // orWhere clause
                $params[] = $condition[3];
            } else {
                if (is_array($condition[2])) {
                    $params = array_merge($params, $condition[2]);
                } else {
                    $params[] = $condition[2];
                }
            }
        }
        return $params;
    }
}
