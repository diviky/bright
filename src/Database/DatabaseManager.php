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

        if ($config['sharding']) {
            $manager = app('bright.shardmanager');
            $manager->setService($config['sharding']);
            $connection = $manager->getShardById(user('id'));

            if ($connection) {
                return $this->connection($connection)->table($name);
            }
        }

        return $this->connection()->table($name);
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
