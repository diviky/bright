<?php

namespace Karla\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Karla\Database\Query\Builder as QueryBuilder;

class Builder extends BaseBuilder
{
    /**
     * Create a new Eloquent query builder instance.
     *
     * @param  \Karla\Database\Query\Builder  $query
     * @return void
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }
}
