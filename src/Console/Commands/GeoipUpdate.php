<?php

namespace Diviky\Bright\Console\Commands;

use Diviky\Bright\Console\Command;
use PragmaRX\Support\GeoIp\Updater as GeoIpUpdater;

class GeoipUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firewall:updategeoip';

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

        $success  = $updater->updateGeoIpFiles(config('firewall.geoip_database_path'));
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
