<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Illuminate\Support\Arr;

trait BuildsQueries
{
    /**
     * @param  array|string  $attributes
     * @return self
     */
    public function whereFilterLike($attributes, string $searchTerm)
    {
        $this->where(function ($query) use ($attributes, $searchTerm) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    str_contains($attribute, '.'),
                    function ($query) use ($attribute, $searchTerm) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);

                        $query->orWhereHas($relationName, function ($query) use ($relationAttribute, $searchTerm) {
                            $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                        });
                    },
                    function ($query) use ($attribute, $searchTerm) {
                        $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                    }
                );
            }
        });

        return $this;
    }

    /**
     * @param  array|string  $attributes
     */
    public function whereFilter(mixed $attributes, ?string $searchTerm, string $condition = '='): self
    {
        $this->where(function ($query) use ($attributes, $searchTerm, $condition) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    str_contains($attribute, '.'),
                    function ($query) use ($attribute, $searchTerm, $condition) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);

                        $query->orWhereHas($relationName, function ($query) use ($relationAttribute, $searchTerm, $condition) {
                            $query->where($relationAttribute, $condition, $searchTerm);
                        });
                    },
                    function ($query) use ($attribute, $searchTerm, $condition) {
                        $query->orWhere($attribute, $condition, $searchTerm);
                    }
                );
            }
        });

        return $this;
    }
}
