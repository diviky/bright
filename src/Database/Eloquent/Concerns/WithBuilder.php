<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Eloquent\Builder as BrightEloquentBuilder;
use Diviky\Bright\Database\Eloquent\Concerns\BuildsQueries as ConcernsBuildsQueries;
use Illuminate\Contracts\Database\Query\Expression as QueryExpression;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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

    public function setModel(Model $model)
    {
        $this->query->setModel($model)->setBuilder($this);

        return parent::setModel($model);
    }

    public function getRelation($name)
    {
        $relation = parent::getRelation($name);

        $this->copyQueryCacheSettings($this->getQuery(), $relation->getQuery(), $name);

        return $relation;
    }

    /**
     * MorphTo eager loads rebuild the related query per type and only merge
     * where-clauses. Copy remember()/cache settings so tokenable (Space) queries
     * stay cached across requests.
     */
    public function mergeConstraintsFrom(EloquentBuilder $from)
    {
        $merged = parent::mergeConstraintsFrom($from);

        $this->copyQueryCacheSettings($from->getQuery(), $merged->getQuery());

        if ($from instanceof BrightEloquentBuilder && $merged instanceof BrightEloquentBuilder) {
            $merged->copyPaginationCountSettings($from);
        }

        return $merged;
    }

    /**
     * Copy Bright query-cache settings from one query builder to another.
     *
     * Accepts either Eloquent or base query builders (MorphTo relations expose
     * the Eloquent builder via getQuery()).
     */
    protected function copyQueryCacheSettings(mixed $from, mixed $to, ?string $relationName = null): void
    {
        $fromBase = $from instanceof EloquentBuilder ? $from->getQuery() : $from;
        $toBase = $to instanceof EloquentBuilder ? $to->getQuery() : $to;

        if (!is_object($fromBase) || !is_object($toBase)) {
            return;
        }

        if (!method_exists($fromBase, 'getCacheTime') || !method_exists($toBase, 'remember')) {
            return;
        }

        $seconds = $fromBase->getCacheTime();

        if (is_null($seconds)) {
            return;
        }

        $key = method_exists($fromBase, 'getCacheKeyName') ? $fromBase->getCacheKeyName() : null;

        if ($key !== null && $relationName !== null) {
            $key = $key . ':' . $relationName;
        }

        $toBase->remember($seconds, $key);
    }

    /**
     * get the value from expression.
     *
     * @param  float|int|QueryExpression|string  $value
     */
    protected function getExpressionValue($value): string
    {
        if ($value instanceof QueryExpression) {
            return (string) $value->getValue($this->getGrammar());
        }

        return (string) $value;
    }

    /**
     * Increment a column without updating the model's `updated_at` timestamp.
     *
     * @param  string|QueryExpression  $column
     * @param  float|int  $amount
     * @param  array<string, mixed>  $extra
     */
    public function plus($column, $amount = 1, array $extra = []): int
    {
        return $this->toBase()->increment($column, $amount, $extra);
    }

    /**
     * Decrement a column without updating the model's `updated_at` timestamp.
     *
     * @param  string|QueryExpression  $column
     * @param  float|int  $amount
     * @param  array<string, mixed>  $extra
     */
    public function minus($column, $amount = 1, array $extra = []): int
    {
        return $this->toBase()->decrement($column, $amount, $extra);
    }

    /**
     * Increment a column without updating timestamps or firing model events.
     *
     * Query builder updates already skip model events; this mirrors the model API.
     *
     * @param  string|QueryExpression  $column
     * @param  float|int  $amount
     * @param  array<string, mixed>  $extra
     */
    public function plusQuietly($column, $amount = 1, array $extra = []): int
    {
        return $this->plus($column, $amount, $extra);
    }

    /**
     * Decrement a column without updating timestamps or firing model events.
     *
     * Query builder updates already skip model events; this mirrors the model API.
     *
     * @param  string|QueryExpression  $column
     * @param  float|int  $amount
     * @param  array<string, mixed>  $extra
     */
    public function minusQuietly($column, $amount = 1, array $extra = []): int
    {
        return $this->minus($column, $amount, $extra);
    }
}
