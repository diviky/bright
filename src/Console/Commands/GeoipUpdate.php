<?php

namespace Diviky\Bright\Console\Commands;

use Illuminate\Console\Command;
use Diviky\Bright\Services\GeoIpUpdater;

class GeoipUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update geo ip database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $updater = new GeoIpUpdater();

        $config = config('bright.geoip');

        if (is_null($config) || is_null($config['update_url'])) {
            return $this->error('Missing configuration');
        }

        $success  = $updater->updateGeoIpFiles($config['database_path'], $config['update_url']);
        $messages = $updater->getMessages();

        foreach ($messages as $message) {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }
    }
}
