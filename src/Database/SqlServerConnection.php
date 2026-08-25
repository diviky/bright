<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\SqlServerConnection as LaravelSqlServerConnection;

class SqlServerConnection extends LaravelSqlServerConnection
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
     * @return SqlServerGrammar
     */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar($this);
        $grammar->setConfig($this->config['bright'] ?? []);

        return $grammar;
    }
}
