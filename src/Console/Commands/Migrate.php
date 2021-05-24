<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Illuminate\Console\Command;

class Migrate extends Command
{
    /**
     * {@inheritDoc}
     */
    protected $signature = 'bright:setup:migrate {--f|force : Force the operation to run when in production.}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Run the database migration files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('migrate', [
            '--path'     => \realpath(__DIR__ . '/../../../database/migrations'),
            '--realpath' => true,
            '--force'    => $this->option('force'),
            '--step'     => true,
        ]);
    }
}
