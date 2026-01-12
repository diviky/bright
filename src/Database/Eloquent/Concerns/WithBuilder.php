<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Eloquent\Concerns\BuildsQueries as ConcernsBuildsQueries;
use Illuminate\Contracts\Database\Query\Expression as QueryExpression;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait WithBuilder
{
    use Async;
    use Batch;
    use BuildsQueries;
    use ConcernsBuildsQueries;
    use Eventable;
    use Filters;
    use Paging;

    /**
     * Create a new Eloquent query builder instance.
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
        $this->sync();
    }

    #[\Override]
    public function setModel(Model $model)
    {
        $this->query->setModel($model)->setBuilder($this);

        return parent::setModel($model);
    }

    #[\Override]
    public function getRelation($name)
    {
        $relation = parent::getRelation($name);

        $cache = $this->getQuery()->getCacheTime();

        if (isset($cache) && is_numeric($cache)) {
            $relation->getQuery()->remember($cache);
        }

        return $relation;
    }

    /**
     * get the value from expression.
     *
     * @param  float|\Illuminate\Contracts\Database\Query\Expression|int|string  $value
     */
    protected function getExpressionValue($value): string
    {
        if ($value instanceof QueryExpression) {
            return (string) $value->getValue($this->getGrammar());
        }

        return (string) $value;
    }
}
