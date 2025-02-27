<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseConnectionManager extends LaravelDatabaseManager
{
    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    #[\Override]
    protected function configuration($name)
    {
        $config = parent::configuration($name);

        $config['bright'] = $this->app['config']['bright'];

        return $config;
    }
}
