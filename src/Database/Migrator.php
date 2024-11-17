<?php

declare(strict_types=1);

namespace SdFramework\Database;

use SdFramework\Application;
use DirectoryIterator;

class Migrator
{
    private Connection $connection;
    private string $table = 'migrations';
    private string $path;

    public function __construct(Connection $connection, string $path)
    {
        $this->connection = $connection;
        $this->path = $path;
        $this->ensureMigrationsTableExists();
    }

    public function migrate(): void
    {
        $migrations = $this->getPendingMigrations();
        
        foreach ($migrations as $migration) {
            $instance = $this->createMigrationInstance($migration);
            $instance->up();
            
            $this->connection->table($this->table)->insert([
                'migration' => $migration,
                'batch' => $this->getNextBatchNumber(),
            ]);
        }
    }

    public function rollback(): void
    {
        $lastBatch = $this->getLastBatchNumber();
        $migrations = $this->getMigrationsForBatch($lastBatch);
        
        foreach (array_reverse($migrations) as $migration) {
            $instance = $this->createMigrationInstance($migration);
            $instance->down();
            
            $this->connection->table($this->table)
                ->where('migration', $migration)
                ->delete();
        }
    }

    public function reset(): void
    {
        $migrations = array_reverse($this->getRanMigrations());
        
        foreach ($migrations as $migration) {
            $instance = $this->createMigrationInstance($migration);
            $instance->down();
            
            $this->connection->table($this->table)
                ->where('migration', $migration)
                ->delete();
        }
    }

    private function ensureMigrationsTableExists(): void
    {
        if (!$this->connection->hasTable($this->table)) {
            $this->connection->statement("
                CREATE TABLE `{$this->table}` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `migration` varchar(255) NOT NULL,
                    `batch` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }

    private function getPendingMigrations(): array
    {
        $files = [];
        $ran = $this->getRanMigrations();
        
        foreach (new DirectoryIterator($this->path) as $file) {
            if ($file->isDot() || $file->getExtension() !== 'php') {
                continue;
            }
            
            $name = $file->getBasename('.php');
            if (!in_array($name, $ran)) {
                $files[] = $name;
            }
        }
        
        sort($files);
        return $files;
    }

    private function getRanMigrations(): array
    {
        return $this->connection->table($this->table)
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    private function getMigrationsForBatch(int $batch): array
    {
        return $this->connection->table($this->table)
            ->where('batch', $batch)
            ->orderBy('migration')
            ->pluck('migration')
            ->toArray();
    }

    private function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    private function getLastBatchNumber(): int
    {
        return (int) $this->connection->table($this->table)
            ->max('batch') ?? 0;
    }

    private function createMigrationInstance(string $name): Migration
    {
        $class = $this->getMigrationClass($name);
        return new $class($this->connection);
    }

    private function getMigrationClass(string $name): string
    {
        require_once $this->path . '/' . $name . '.php';
        return 'Database\\Migrations\\' . $name;
    }
}
