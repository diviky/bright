<?php

namespace Diviky\Bright\Console\Commands;

use Diviky\Bright\Console\Command;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bright:setup:migrate';

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
        $this->call('migrate', [
            '--path'     => \realpath(__DIR__ . '/../../../database/migrations'),
            '--realpath' => true,
        ]);
    }
}
