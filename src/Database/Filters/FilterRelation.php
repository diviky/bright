<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FilterRelation implements Filter
{
    /**
     * @var array
     */
    protected $relationConstraints = [];

    /**
     * @var string
     */
    protected $condition = '=';

    /**
     * @var string
     */
    protected $separator = '.';

    public function __construct(string $condition = '=', string $separator = '.')
    {
        $this->condition = $condition;
        $this->separator = $separator;
    }

    /**
     * @param  mixed  $value
     */
    #[\Override]
    public function __invoke(Builder $query, $value, string $property): void
    {
        if ($this->isRelationProperty($query, $property)) {
            $this->withRelationConstraint($query, $value, $property);

            return;
        }

        if (Str::contains($property, '|')) {
            $columns = \explode('|', $property);
            $query->where(function ($query) use ($columns, $value): void {
                foreach ($columns as $column) {
                    $query->orWhere($column, $this->condition, $value);
                }
            });

            return;
        }

        if (is_array($value)) {
            $query->whereIn($query->qualifyColumn($property), $value);

            return;
        }

        $query->where($query->qualifyColumn($property), $this->condition, $value);
    }

    /**
     * Set the value of separator.
     *
     * @return self
     */
    public function setSeparator(string $separator)
    {
        $this->separator = $separator;

        return $this;
    }

    protected function isRelationProperty(Builder $query, string $property): bool
    {
        if (!Str::contains($property, $this->separator)) {
            return false;
        }

        if (in_array($property, $this->relationConstraints)) {
            return false;
        }

        $firstRelationship = Str::camel(explode($this->separator, $property)[0]);

        if (!method_exists($query->getModel(), $firstRelationship)) {
            return false;
        }

        return is_a($query->getModel()->{$firstRelationship}(), Relation::class);
    }

    /**
     * @param  mixed  $value
     */
    protected function withRelationConstraint(Builder $query, $value, string $property): void
    {
        [$relation, $property] = collect(explode($this->separator, $property))
            ->pipe(function (Collection $parts) {
                $property = $parts->pop();

                return [
                    $parts->except($parts->all())->map([Str::class, 'camel'])->implode('.'),
                    $property,
                ];
            });

        $query->whereHas($relation, function (Builder $query) use ($value, $property): void {
            $this->relationConstraints[] = $property = $query->qualifyColumn($property);

            $this->__invoke($query, $value, $property);
        });
    }
}
