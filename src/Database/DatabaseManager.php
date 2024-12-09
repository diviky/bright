<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connector;
use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseManager extends LaravelDatabaseManager
{
    use Connector;

    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        if ($config['driver'] === 'mongodb') {
            return $this->factory->make($config, $name);
        }

        // First we will check by the connection name to see if an extension has been
        // registered specifically for that connection. If it has we will call the
        // Closure and pass it the config allowing it to resolve the connection.
        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }

        // Next we will check to see if an extension has been registered for a driver
        // and will call the Closure if so, which allows us to have a more generic
        // resolver for the drivers themselves which applies to all connections.
        if (isset($this->extensions[$driver = $config['driver']])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }

        return $this->factory->make($config, $name);
    }

    public function extend($name, callable $resolver, bool $overwrite = false)
    {
        if (!isset($this->extension[$name]) || $overwrite) {
            $this->extensions[$name] = $resolver;
        }
    }

    /**
     * Database table.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($name)
    {
        $alias = '';
        if (\stripos($name, ' as ') !== false) {
            $segments = \preg_split('/\s+as\s+/i', $name);
            $alias = ' as ' . $segments[1];
            $name = $segments[0];
        }

        return $this->getConnectionByTable($name)->table($name . $alias);
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnectionByTable(string $name)
    {
        [$connection, $config] = $this->getConnectionDetails($name);

        $connection = $this->connection($connection);
        $connection->getQueryGrammar()->setConfig($config);

        return $connection;
    }

    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration($name)
    {
        $config = parent::configuration($name);

        $config['bright'] = $this->getBrightConfig();

        return $config;
    }
}
