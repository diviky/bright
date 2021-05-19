<?php

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }
}
