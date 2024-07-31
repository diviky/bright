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

        return match ($config['driver']) {
            'mysql' => new MySqlConnector,
            'pgsql' => new PostgresConnector,
            default => parent::createConnector($config),
        };
    }

    /**
     * Create a new connection instance.
     *
     * @param  string  $driver
     * @param  \Closure|\PDO  $connection
     * @param  string  $database
     * @param  string  $prefix
     * @return Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        $resolver = Connection::getResolver($driver);

        if ($resolver) {
            return $resolver($connection, $database, $prefix, $config);
        }

        return match ($driver) {
            'mysql' => new MySqlConnection($connection, $database, $prefix, $config),
            'sqlite' => new SQLiteConnection($connection, $database, $prefix, $config),
            'mongodb' => new \Diviky\Bright\Database\MongoDB\Connection($config),
            default => parent::createConnection($driver, $connection, $database, $prefix, $config),
        };
    }
}
