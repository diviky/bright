<?php

namespace Karla\Database;

use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseManager extends LaravelDatabaseManager
{
    public function table($name)
    {
        $config      = $this->app['config']['karla'];
        $connections = $config['connections'];

        if (is_array($connections) && isset($connections[$name])) {
            return $this->connection($connections[$name])->table($name);
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

        $config['karla'] = $this->app['config']['karla'];

        return $config;
    }
}
