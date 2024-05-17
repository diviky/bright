<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

trait Build
{
    /**
     * Add a where between statement to the query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @param  array|string  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return static
     */
    public function whereDateBetween($column, $values, $boolean = 'and', $not = false)
    {
        if (!\is_array($values)) {
            return parent::whereDate($column, $values);
        }

        $column = $this->raw('DATE(' . $this->grammar->wrap($column) . ')');

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  array|\Closure|\Illuminate\Contracts\Database\Query\Expression|string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $val = \func_num_args() === 2 ? $operator : $value;
        if (\is_array($val)) {
            return parent::whereIn($column, $val);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Delete a record from the database.
     *
     * @return array|bool|int
     */
    public function deletes()
    {
        $query = $this->toQuery();

        return $this->statement('DELETE ' . \substr($query, 6));
    }

    /**
     * @param  string|array  $attributes
     * @return self
     */
    public function whereLike($attributes, string $searchTerm)
    {
        $this->where(function (Builder $query) use ($attributes, $searchTerm) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    str_contains($attribute, '.'),
                    function (Builder $query) use ($attribute, $searchTerm) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);

                        $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                            $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                        });
                    },
                    function (Builder $query) use ($attribute, $searchTerm) {
                        $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                    }
                );
            }
        });

        return $this;
    }
}
