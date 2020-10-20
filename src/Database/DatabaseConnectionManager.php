<?php

namespace Diviky\Bright\Database;

use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseConnectionManager extends LaravelDatabaseManager
{
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
