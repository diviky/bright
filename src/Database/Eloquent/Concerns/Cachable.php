<?php

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Database\Eloquent\Builder as EloquentBuilder;
use Diviky\Bright\Database\Eloquent\Collection as BrightCollection;
use Diviky\Bright\Database\Query\Builder as BaseQueryBuilder;

trait Cachable
{
    /**
     * Create a new Eloquent Collection instance.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new BrightCollection($models);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new BaseQueryBuilder($conn, $grammar, $conn->getPostProcessor());

        // Disable the timestamps addition to eloquent
        $builder->timestamps(false);

        if (isset($this->eventState)) {
            $builder->eventState($this->eventState);
        }

        if (isset($this->rememberFor)) {
            $builder->remember($this->rememberFor);
        }

        if (isset($this->rememberCacheTag)) {
            $builder->cacheTags($this->rememberCacheTag);
        }

        if (isset($this->rememberCachePrefix)) {
            $builder->cachePrefix($this->rememberCachePrefix);
        }

        if (isset($this->rememberCacheDriver)) {
            $builder->cacheDriver($this->rememberCacheDriver);
        }

        return $builder;
    }
}
