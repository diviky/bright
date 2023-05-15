<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Eloquent\Concerns\Async;
use Diviky\Bright\Database\Eloquent\Concerns\Batch;
use Diviky\Bright\Database\Eloquent\Concerns\Eventable;
use Diviky\Bright\Database\Eloquent\Concerns\Filters;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Builder extends BaseBuilder
{
    use BuildsQueries;
    use Paging;
    use Filters;
    use Async;
    use Batch;
    use Eventable;

    /**
     * Create a new Eloquent query builder instance.
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
        $this->sync();
    }

    /**
     * {@inheritDoc}
     */
    public function setModel(Model $model)
    {
        $this->query->setModel($model)->setBuilder($this);

        return parent::setModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getRelation($name)
    {
        $relation = parent::getRelation($name);

        $cache = $this->getQuery()->getCacheTime();

        if (isset($cache) && is_numeric($cache)) {
            $relation->getQuery()->remember($cache);
        }

        return $relation;
    }
}
