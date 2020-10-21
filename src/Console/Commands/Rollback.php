<?php

namespace Diviky\Bright\Console\Commands;

use Illuminate\Console\Command;

class Rollback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bright:setup:migrate:rollback {--f|force : Force the operation to run when in production.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migration files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('migrate:reset', [
            '--path'     => \realpath(__DIR__ . '/../../../database/migrations'),
            '--realpath' => true,
            '--force'    => $this->option('force'),
        ]);
    }
}
