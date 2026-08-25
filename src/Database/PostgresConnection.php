<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Illuminate\Database\PostgresConnection as LaravelPostgresConnection;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

class PostgresConnection extends LaravelPostgresConnection
{
    use Connection;

    /**
     * Get a new query builder instance.
     *
     * @return QueryBuilder
     */
    #[\Override]
    public function query()
    {
        $builder = new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );

        $builder->setConfig($this->config['bright'] ?? []);

        return $builder;
    }

    /**
     * Get the default query grammar instance.
     *
     * @return PostgresGrammar
     */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar($this);
        $grammar->setConfig($this->config['bright'] ?? []);

        return $grammar;
    }
}
