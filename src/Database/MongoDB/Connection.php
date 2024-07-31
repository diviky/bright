<?php

namespace Diviky\Bright\Database\MongoDB;

use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\Grammar;
use MongoDB\Laravel\Connection as MongoDBConnection;

class Connection extends MongoDBConnection
{
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /** {@inheritdoc} */
    protected function getDefaultQueryGrammar()
    {
        return new Grammar;
    }
}
