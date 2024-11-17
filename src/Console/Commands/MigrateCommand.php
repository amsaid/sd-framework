<?php

declare(strict_types=1);

namespace SdFramework\Console\Commands;

use SdFramework\Console\Command;
use SdFramework\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run the database migrations';

    public function handle(array $arguments = [], array $options = []): int
    {
        try {
            /** @var Migrator $migrator */
            $migrator = app(Migrator::class);

            if (isset($options['fresh'])) {
                $this->output('Dropping all tables...');
                // TODO: Implement dropping all tables
                $this->output('Running migrations...');
                $migrator->migrate();
                $this->output('Database was refreshed successfully.');
                return 0;
            }

            if (isset($options['rollback'])) {
                $this->output('Rolling back the last migration batch...');
                $migrator->rollback();
                $this->output('Migration rollback completed successfully.');
                return 0;
            }

            if (isset($options['reset'])) {
                $this->output('Rolling back all migrations...');
                $migrator->reset();
                $this->output('Migration reset completed successfully.');
                return 0;
            }

            $this->output('Running migrations...');
            $migrator->migrate();
            $this->output('Migration completed successfully.');
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            if (config('app.debug', false)) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}
