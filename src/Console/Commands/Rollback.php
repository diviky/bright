<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * {@inheritDoc}
     */
    protected $signature = 'bright:setup:migrate:rollback {--f|force : Force the operation to run when in production.}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Run the database migration files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('migrate:reset', [
            '--path' => \realpath(__DIR__ . '/../../../database/migrations'),
            '--realpath' => true,
            '--force' => $this->option('force'),
        ]);
    }
}
