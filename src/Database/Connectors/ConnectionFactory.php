<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Connectors;

use Diviky\Bright\Database\MySqlConnection;
use Diviky\Bright\Database\Octane\MySqlStringBindingConnection;
use Diviky\Bright\Database\PostgresConnection;
use Diviky\Bright\Database\QueryGrammarConfigurator;
use Diviky\Bright\Database\SQLiteConnection;
use Diviky\Bright\Database\SqlServerConnection;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory as LaravelConnectionFactory;
use Illuminate\Database\Connectors\ConnectorInterface;

class ConnectionFactory extends LaravelConnectionFactory
{
    /**
     * Create a connector instance based on the configuration.
     *
     * @return ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    #[\Override]
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
    #[\Override]
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        $resolver = Connection::getResolver($driver);

        if ($resolver) {
            $resolved = $resolver($connection, $database, $prefix, $config);

            $resolved = $this->upgradeToBrightMySqlConnection($driver, $connection, $database, $prefix, $config, $resolved);

            QueryGrammarConfigurator::apply($resolved, $config['bright'] ?? []);

            return $resolved;
        }

        $resolved = match ($driver) {
            'mysql', 'mariadb' => new MySqlConnection($connection, $database, $prefix, $config),
            'sqlite' => new SQLiteConnection($connection, $database, $prefix, $config),
            'pgsql' => new PostgresConnection($connection, $database, $prefix, $config),
            'sqlsrv' => new SqlServerConnection($connection, $database, $prefix, $config),
            'mongodb' => new \Diviky\Bright\Database\MongoDB\Connection($config),
            default => parent::createConnection($driver, $connection, $database, $prefix, $config),
        };

        if ($resolved instanceof Connection) {
            QueryGrammarConfigurator::apply($resolved, $config['bright'] ?? []);
        }

        return $resolved;
    }

    /**
     * Ensure Octane's MySQL connection resolver product includes Bright helpers.
     *
     * @param  \Closure|\PDO  $connection
     */
    protected function upgradeToBrightMySqlConnection(
        string $driver,
        $connection,
        string $database,
        string $prefix,
        array $config,
        Connection $resolved,
    ): Connection {
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return $resolved;
        }

        if ($resolved instanceof MySqlStringBindingConnection || $resolved instanceof MySqlConnection) {
            return $resolved;
        }

        if ($resolved instanceof \Laravel\Octane\Swoole\Database\MySqlStringBindingConnection) {
            return new MySqlStringBindingConnection($connection, $database, $prefix, $config);
        }

        return $resolved;
    }
}
