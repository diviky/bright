<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Illuminate\Database\SQLiteConnection as LaravelSQLiteConnection;

class SQLiteConnection extends LaravelSQLiteConnection
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
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\SQLiteGrammar
     */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar($this);
        $grammar->setConfig($this->config['bright'] ?? []);

        return $grammar;
    }
}
