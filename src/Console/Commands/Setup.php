<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Setup extends Command
{
    protected $signature = 'bright:setup';

    protected $description = 'Setup the bright package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ($this->confirm('Do you wish to replace required files?', true)) {
            $this->shell("sed -i '' 's/Illuminate\\Foundation\\Auth\\/Diviky\\Bright\\Models\\/g' app/Models/User.php");
            $this->shell("sed -i '' 's/Illuminate\\Foundation\\/Diviky\\Bright\\/g' app/Exceptions/Handler.php");
            $this->shell("sed -i '' 's/Illuminate\\Routing\\/Diviky\\Bright\\Routing\\/g' app/Http/Controllers/Controller.php");
        }

        if ($this->confirm('Do you wish to publish config?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-config',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish app boostrap js?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-assets-app',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish js assets?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-assets-js',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish migrations?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-migrations',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish seeders?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-seeders',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish auth views?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-views-auth',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish vendor views?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-views-vendor',
                '--force' => 1,
            ]);
        }

        if ($this->confirm('Do you wish to publish setup files?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'bright-setup',
                '--force' => 1,
            ]);

            if ($this->confirm('Do you want to install js packages?', true)) {
                $this->shell('npm install && npm run dev');
            }
        }
    }

    /**
     * Execute the command.
     *
     * @param  string  $command
     */
    protected function shell($command): void
    {
        $command = \str_replace('\\', '\\\\', $command);
        $this->info('running... ' . $command);

        $process = Process::fromShellCommandline($command);

        $process->setTimeout(10 * 60);
        $process->run(function ($type, $buffer): void {
            if ($type === Process::ERR) {
                echo 'ERR > ' . $buffer;
            } else {
                echo 'OUT > ' . $buffer;
            }
        });
    }
}
