<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Illuminate\Database\MySqlConnection as LaravelMySqlConnection;

class MySqlConnection extends LaravelMySqlConnection
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
     * @return \Illuminate\Database\Query\Grammars\MySqlGrammar
     */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar;
        $grammar->setConfig($this->config['bright'] ?? []);

        return $this->withTablePrefix($grammar);
    }
}
