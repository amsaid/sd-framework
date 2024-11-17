<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use SdFramework\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate 
        {--fresh : Drop all tables and re-run all migrations}
        {--rollback : Rollback the last database migration}
        {--reset : Rollback all database migrations}';
    
    protected string $description = 'Run the database migrations';

    public function handle(): int
    {
        $migrator = $this->app->make(Migrator::class);

        if ($this->option('fresh')) {
            $this->info('Dropping all tables...');
            // TODO: Implement dropping all tables
            $this->info('Running migrations...');
            $migrator->migrate();
            $this->info('Database was refreshed successfully.');
            return 0;
        }

        if ($this->option('rollback')) {
            $this->info('Rolling back the last migration batch...');
            $migrator->rollback();
            $this->info('Migration rollback completed successfully.');
            return 0;
        }

        if ($this->option('reset')) {
            $this->info('Rolling back all migrations...');
            $migrator->reset();
            $this->info('Migration reset completed successfully.');
            return 0;
        }

        $this->info('Running migrations...');
        $migrator->migrate();
        $this->info('Migration completed successfully.');
        return 0;
    }
}
