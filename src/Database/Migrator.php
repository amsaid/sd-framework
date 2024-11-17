<?php

declare(strict_types=1);

namespace SdFramework\Database;

use SdFramework\Database\Migration;
use SdFramework\Database\QueryBuilder;

class Migrator
{
    private QueryBuilder $db;
    private string $migrationsPath;
    private string $table = 'migrations';

    public function __construct(QueryBuilder $db)
    {
        $this->db = $db;
        $this->migrationsPath = base_path() . '/'.config('schema.migrations_path', 'database/migrations');
        $this->table = config('schema.migrations_table', $this->table);
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        if (!$this->db->tableExists($this->table)) {
            $this->db->createTable($this->table, function ($table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
                $table->timestamps();
            });
        }
    }

    public function migrate(): void
    {
        // Get ran migrations
        $ran = $this->getRanMigrations();
        
        // Get all migration files
        $files = $this->getMigrationFiles();
        
        // Get pending migrations
        $pending = array_diff($files, $ran);
        
        if (empty($pending)) {
            return;
        }

        // Get next batch number
        $batch = $this->getNextBatchNumber();

        // Run pending migrations
        foreach ($pending as $file) {
            $this->runMigration($file, $batch);
        }
    }

    public function rollback(): void
    {
        $lastBatch = $this->getLastBatchNumber();
        
        if ($lastBatch === 0) {
            return;
        }

        $migrations = $this->db->table($this->table)
            ->where('batch', $lastBatch)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration->migration);
        }
    }

    public function reset(): void
    {
        $migrations = $this->db->table($this->table)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration->migration);
        }
    }

    private function runMigration(string $file, int $batch): void
    {
        $migration = $this->loadMigration($file);
        
        if ($migration) {
            $migration->up();
            
            $this->db->table($this->table)->insert([
                'migration' => $file,
                'batch' => $batch,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function rollbackMigration(string $file): void
    {
        $migration = $this->loadMigration($file);
        
        if ($migration) {
            $migration->down();
            
            $this->db->table($this->table)
                ->where('migration', $file)
                ->delete();
        }
    }

    private function loadMigration(string $file): ?Migration
    {
        $path = $this->migrationsPath . '/' . $file;
        
        if (!file_exists($path)) {
            return null;
        }

        // Load the migration file which returns a Migration instance
        $migration = require $path;

        if (!$migration instanceof Migration) {
            throw new \RuntimeException("Migration file {$file} must return an instance of " . Migration::class);
        }

        return $migration;
    }

    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        return array_filter($files, function ($file) {
            return !in_array($file, ['.', '..']) && str_ends_with($file, '.php');
        });
    }

    private function getRanMigrations(): array
    {
        return $this->db->table($this->table)
            ->pluck('migration');
    }

    private function getNextBatchNumber(): int
    {
        $lastBatch = $this->getLastBatchNumber();
        return $lastBatch + 1;
    }

    private function getLastBatchNumber(): int
    {
        return (int) $this->db->table($this->table)
            ->max('batch') ?? 0;
    }
}
