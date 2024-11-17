<?php

declare(strict_types=1);

namespace SdFramework\Database;

use PDO;
use PDOException;
use SdFramework\Database\DatabaseException;

class Connection
{
    private PDO $pdo;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['type'] ?? 'mysql',
            $this->config['host'] ?? 'localhost',
            $this->config['port'] ?? '3306',
            $this->config['name'],
            $this->config['charset'] ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new DatabaseException(
                "Failed to connect to database: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    public function query(string $sql, array $params = []): QueryBuilder
    {
        return new QueryBuilder($this, $sql, $params);
    }

    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

    public function statement(string $sql, array $params = []): bool
    {
        try {
            if (empty($params)) {
                return $this->pdo->exec($sql) !== false;
            }

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new DatabaseException(
                "Failed to execute SQL statement: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
