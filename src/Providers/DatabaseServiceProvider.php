<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Diviky\Bright\Database\Connectors\ConnectionFactory;
use Diviky\Bright\Database\DatabaseManager;
use Diviky\Bright\Database\LostConnectionDetector;
use Diviky\Bright\Database\MongoDB\Connection as MongoConnection;
use Diviky\Bright\Database\Octane\DatabaseManager as OctaneDatabaseManager;
use Diviky\Bright\Database\Octane\MySqlStringBindingConnection;
use Illuminate\Contracts\Database\LostConnectionDetector as LostConnectionDetectorContract;
use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(LostConnectionDetectorContract::class, LostConnectionDetector::class);

        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        $this->app->resolving('db', function ($db) {
            $db->extend('mongodb', function ($config, $name) {
                $config['name'] = $name;

                return new MongoConnection($config);
            });
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerOctaneDatabaseManager();
        $this->registerOctaneMySqlConnectionResolver();
    }

    /**
     * Layer Bright routing on Octane's pooled database manager without patching Octane.
     */
    protected function registerOctaneDatabaseManager(): void
    {
        if (!class_exists(\Laravel\Octane\Swoole\Database\DatabaseManager::class)) {
            return;
        }

        $this->app->extend('db', function ($manager, $app) {
            if ($manager instanceof OctaneDatabaseManager) {
                return $manager;
            }

            return new OctaneDatabaseManager($app, $app['db.factory']);
        });
    }

    /**
     * Replace Octane's MySQL connection with one that includes Bright query/async behavior.
     */
    protected function registerOctaneMySqlConnectionResolver(): void
    {
        if (!class_exists(\Laravel\Octane\Swoole\Database\MySqlStringBindingConnection::class)) {
            return;
        }

        $stringBindings = filter_var(config('octane.mysql_string_bindings', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
        $statementCache = filter_var(config('octane.mysql_statement_cache', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;

        if (!$stringBindings && !$statementCache) {
            return;
        }

        DatabaseConnection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlStringBindingConnection($connection, $database, $prefix, $config);
        });

        DatabaseConnection::resolverFor('mariadb', function ($connection, $database, $prefix, $config) {
            return new MySqlStringBindingConnection($connection, $database, $prefix, $config);
        });
    }
}
