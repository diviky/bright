<?php

namespace Diviky\Bright\Database\MongoDB\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\WithModel;
use Diviky\Bright\Database\MongoDB\Builder as QueryBuilder;
use MongoDB\Laravel\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use WithModel;

    /**
     * @return \Diviky\Bright\Database\MongoDB\Eloquent\Builder
     */
    #[\Override]
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /** {@inheritdoc} */
    #[\Override]
    public function qualifyColumn($column)
    {
        return $column;
    }

    /** {@inheritdoc} */
    #[\Override]
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
