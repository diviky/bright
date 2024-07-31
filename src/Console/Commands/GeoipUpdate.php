<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Diviky\Bright\Services\GeoIpUpdater;
use Illuminate\Console\Command;

class GeoipUpdate extends Command
{
    protected $signature = 'geoip:update';

    protected $description = 'Update geo ip database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updater = new GeoIpUpdater;

        $config = config('bright.geoip');

        if (is_null($config) || is_null($config['update_url'])) {
            $this->error('Missing configuration');

            return 1;
        }

        $success = $updater->updateGeoIpFiles($config['database_path'], $config['update_url']);
        $messages = $updater->getMessages();

        foreach ($messages as $message) {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }

        return 0;
    }
}
