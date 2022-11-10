<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Connectors;

use Diviky\Bright\Database\MySqlConnection;
use Diviky\Bright\Database\SQLiteConnection;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory as LaravelConnectionFactory;

class ConnectionFactory extends LaravelConnectionFactory
{
    /**
     * Create a connector instance based on the configuration.
     *
     * @return \Illuminate\Database\Connectors\ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        $key = "db.connector.{$config['driver']}";

        if ($this->container->bound($key)) {
            return $this->container->make($key);
        }

        switch ($config['driver']) {
            case 'mysql':
                return new MySqlConnector();
            case 'pgsql':
                return new PostgresConnector();
        }

        return parent::createConnector($config);
    }

    /**
     * Create a new connection instance.
     *
     * @param string        $driver
     * @param \Closure|\PDO $connection
     * @param string        $database
     * @param string        $prefix
     *
     * @return \Illuminate\Database\Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        $resolver = Connection::getResolver($driver);

        if ($resolver) {
            return $resolver($connection, $database, $prefix, $config);
        }

        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
            case 'sqlite':
                return new SQLiteConnection($connection, $database, $prefix, $config);
        }

        return parent::createConnection($driver, $connection, $database, $prefix, $config);
    }
}
