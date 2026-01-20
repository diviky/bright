<?php

namespace Diviky\Bright\Database\MongoDB;

use Diviky\Bright\Database\Concerns\Connection as ConcernsConnection;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\MongoDBGrammar;
use MongoDB\Laravel\Connection as MongoDBConnection;

class Connection extends MongoDBConnection
{
    use ConcernsConnection;

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    #[\Override]
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /** {@inheritdoc} */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        return new MongoDBGrammar($this);
    }
}
