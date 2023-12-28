<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Sharding\ShardManager;
use Illuminate\Support\Facades\App;

trait Connector
{
    /**
     * Get a database connection instance from shard.
     *
     * @param  null|string  $shard_key
     */
    protected function shard($shard_key = null, array $config = []): array
    {
        $manager = $this->getShardManager($config);

        if (!$manager) {
            return [null, []];
        }

        $shard_val = null;
        if (isset($shard_key) && app()->has($shard_key)) {
            $shard_val = app($shard_key);
        }

        $shard_val = $shard_val ?? user('id');

        $connection = $shard_val ? $manager->getShardById($shard_val) : null;
        if ($connection) {
            $config = $manager->getShardConfig();

            return [$connection, $config['connection']];
        }

        return [null, []];
    }

    protected function getShardManager(array $config): ?ShardManager
    {
        if (!empty($config['sharding'])) {
            $manager = App::make('bright.shardmanager');
            $manager->setService($config['sharding']);

            return $manager;
        }

        return null;
    }

    protected function getConnectionDetails(string $table): array
    {
        $config = $this->getBrightConfig();

        $connections = $config['connections'] ?? [];

        $connection = null;
        if (\is_array($connections)) {
            $patterns = $connections['patterns'] ?? [];

            if (isset($connections['names']) && \is_array($connections['names']) && isset($connections['names'][$table])) {
                $connection = $connections['names'][$table];
            } elseif (\is_array($patterns)) {
                foreach ($patterns as $pattern => $database) {
                    if (preg_match('/^' . $pattern . '/', $table)) {
                        if (\is_array($database)) {
                            $database = $database[0];
                        }

                        $connection = $database;

                        break;
                    }
                }
            }
        }

        if (!is_null($connection)) {
            $config['databases'] = [];

            return [$connection, $config];
        }

        $shard_key = $config['shard_key'] ?? null;

        return $this->shard($shard_key, $config);
    }

    protected function getBrightConfig(): array
    {
        $config = App::make('config')->get('bright');

        return [
            'databases' => $config['databases'],
            'tables' => $config['tables'],
            'connections' => $config['connections'],
            'async' => $config['async'],
            'sharding' => $config['sharding'],
            'timestamps' => $config['timestamps'],
            'db_events' => $config['db_events'],
            'db_cache' => $config['db_cache'],
        ];
    }
}
