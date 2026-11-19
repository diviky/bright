<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Query\Grammars\MySqlGrammar;
use Diviky\Bright\Database\Query\Grammars\PostgresGrammar;
use Diviky\Bright\Database\Query\Grammars\SQLiteGrammar;
use Diviky\Bright\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Connection;

class QueryGrammarConfigurator
{
    /**
     * Apply global Bright config from the application to a connection's query grammar.
     *
     * Used when the connection was created without Bright's database manager (for example
     * Hypervel's pooled resolver). Safe on Laravel: idempotent with factory-time apply.
     */
    public static function applyFromApplicationConfig(Connection $connection): void
    {
        if (!function_exists('config')) {
            return;
        }

        $bright = config('bright');

        if (!is_array($bright) || $bright === []) {
            return;
        }

        self::apply($connection, [
            'databases' => $bright['databases'] ?? [],
            'tables' => $bright['tables'] ?? [],
            'connections' => $bright['connections'] ?? [],
            'async' => $bright['async'] ?? false,
            'sharding' => $bright['sharding'] ?? false,
            'timestamps' => $bright['timestamps'] ?? false,
            'db_events' => $bright['db_events'] ?? false,
            'db_cache' => $bright['db_cache'] ?? false,
        ]);
    }

    /**
     * Apply Bright database routing config to the connection's query grammar.
     *
     * Extended connection resolvers (for example Octane's MySQL connection) may
     * still use Laravel's default grammars, which do not implement setConfig().
     */
    public static function apply(Connection $connection, array $brightConfig = []): void
    {
        if ($brightConfig === []) {
            $brightConfig = $connection->getConfig()['bright'] ?? [];
        }

        $grammar = $connection->getQueryGrammar();

        if (method_exists($grammar, 'setConfig')) {
            $grammar->setConfig($brightConfig);

            return;
        }

        $replacement = self::grammarFor($connection);

        if ($replacement === null) {
            return;
        }

        $replacement->setConfig($brightConfig);
        $connection->setQueryGrammar($replacement);
    }

    protected static function grammarFor(Connection $connection): MySqlGrammar|PostgresGrammar|SQLiteGrammar|SqlServerGrammar|null
    {
        return match ($connection->getDriverName()) {
            'mysql', 'mariadb' => new MySqlGrammar($connection),
            'pgsql' => new PostgresGrammar($connection),
            'sqlite' => new SQLiteGrammar($connection),
            'sqlsrv' => new SqlServerGrammar($connection),
            default => null,
        };
    }
}
