<?php

namespace Karla\Console\Commands;

use Karla\Console\Command;
use Symfony\Component\Process\Process;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karla:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the karla package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Do you wish to replace required files?', true)) {
            $this->shell("sed -i '' 's/Illuminate\\\\Foundation\\\\Auth\\\\/Karla\\\\Models\\\\/g' app/Models/User.php");
            $this->shell("sed -i '' 's/Illuminate\\\\Foundation\\\\/Karla\\\\/g' app/Exceptions/Handler.php");
            $this->shell("sed -i '' 's/Illuminate\\\\Routing\\\\/Karla\\\\Routing\\\\/g' app/Http/Controllers/Controller.php");
        }

        if ($this->confirm('Do you wish to publish config?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'karla-config',
            ]);
        }

        if ($this->confirm('Do you wish to publish app boostrap js?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'karla-config',
            ]);
        }

        if ($this->confirm('Do you wish to publish setup files?', true)) {
            $this->call('vendor:publish', [
                '--tag'   => 'karla-setup',
                '--force' => 1,
            ]);

            if ($this->confirm('Do you want to install js packages?', true)) {
                $this->shell('bower install');
                $this->shell('npm run dev');
            }
        }
    }

    protected function shell($command)
    {
        $this->info('running... ' . $command);
        if ('WIN' === \strtoupper(\substr(PHP_OS, 0, 3))) {
            $process = Process::fromShellCommandline($command);
        } else {
            $process = Process::fromShellCommandline($command);
        }

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > ' . $buffer;
            } else {
                echo 'OUT > ' . $buffer;
            }
        });
    }
}
