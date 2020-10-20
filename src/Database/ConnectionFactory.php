<?php

namespace Diviky\Bright\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory as LaravelConnectionFactory;

class ConnectionFactory extends LaravelConnectionFactory
{
    /**
     * Create a new connection instance.
     *
     * @param string        $driver
     * @param \Closure|\PDO $connection
     * @param string        $database
     * @param string        $prefix
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Database\Connection
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ('mysql' !== $driver) {
            return parent::createConnection($driver, $connection, $database, $prefix, $config);
        }

        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        return new MySqlConnection($connection, $database, $prefix, $config);
    }
}
