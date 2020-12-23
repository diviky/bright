<?php

namespace Diviky\Bright\Database;

use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseManager extends LaravelDatabaseManager
{
    public function table($name)
    {
        $config      = $this->app['config']['bright'];
        $connections = $config['connections'];

        if (\is_array($connections) && \is_string($name) && isset($connections[$name])) {
            return $this->connection($connections[$name])->table($name);
        }

        return $this->shard()->table($name);
    }

    public function shard($shard_key = null)
    {
        $manager = $this->getShardManager();

        if ($manager) {
            $shard_key  = $shard_key ?? user('id');
            $connection = $shard_key ? $manager->getShardById($shard_key) : null;
            if ($connection) {
                return $this->connection($connection);
            }
        }

        return $this->connection();
    }

    public function getShardManager()
    {
        $config  = $this->app['config']['bright'];

        if ($config['sharding']) {
            $manager = $this->app['bright.shardmanager'];
            $manager->setService($config['sharding']);

            return $manager;
        }

        return null;
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function configuration($name)
    {
        $config = parent::configuration($name);

        $config['bright'] = $this->app['config']['bright'];

        return $config;
    }
}
