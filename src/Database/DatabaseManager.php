<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Sharding\ShardManager;
use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;
use Illuminate\Database\Query\Expression;

class DatabaseManager extends LaravelDatabaseManager
{
    /**
     * Database table.
     *
     * @param \Illuminate\Database\Query\Expression|string $name
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($name)
    {
        $config = $this->app['config']['bright'];
        $connections = $config['connections'];

        if ($name instanceof Expression) {
            $name = $name->getValue();
        }

        $alias = '';
        if (false !== \stripos($name, ' as ')) {
            $segments = \preg_split('/\s+as\s+/i', $name);
            $alias = ' as ' . $segments[1];
            $name = $segments[0];
        }

        if (\is_array($connections) && isset($connections[$name])) {
            $connection = $this->connection($connections[$name]);
        } else {
            $connection = $this->shard();
        }

        $connection->getQueryGrammar()->setConfig($config);

        return $connection->table($name . $alias);
    }

    /**
     * Get a database connection instance from shard.
     *
     * @param null|string $shard_key
     */
    public function shard($shard_key = null): \Illuminate\Database\Connection
    {
        $manager = $this->getShardManager();

        if ($manager) {
            $shard_key = $shard_key ?? user('id');
            $connection = $shard_key ? $manager->getShardById($shard_key) : null;
            if ($connection) {
                $config = $manager->getShardConfig();
                $connection = $this->connection($connection);
                $connection->getQueryGrammar()->setConfig($config['connection']);

                return $connection;
            }
        }

        return $this->connection();
    }

    public function getShardManager(): ?ShardManager
    {
        $config = $this->app['config']['bright'];

        if (!empty($config['sharding'])) {
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
