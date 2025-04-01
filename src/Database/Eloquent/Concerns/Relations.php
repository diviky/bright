<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait Relations
{
    /**
     * Merge the relations attributes.
     *
     * @return static
     */
    public function flatten(array $except = [], array $exclude = [])
    {
        return $this->flattenRelations($this->getRelations(), $except, $exclude);
    }

    /**
     * Merge the relations attributes.
     *
     * @return static
     */
    public function flat(array $except = [], array $exclude = [])
    {
        return $this->flatRelations($this->getRelations(), $except, $exclude);
    }

    /**
     * Merge the relations attributes.
     *
     * @return static
     */
    public function collapse(array $except = [], array $exclude = [])
    {
        return $this->flat($except, $exclude);
    }

    /**
     * Returns only the models from the collection with the specified keys.
     *
     * @param  mixed  $keys
     * @return static
     */
    public function some(array $keys)
    {
        $this->attributes = Arr::only($this->attributes, $keys);

        return $this;
    }

    /**
     * Merge keys into the attributes.
     *
     * @return static
     */
    public function merge(array $keys = [])
    {
        if (!Arr::isAssoc($keys)) {
            $keys = array_fill_keys($keys, null);
        }

        $relations = Arr::undot($keys);

        foreach ($relations as $relation_key => $relation) {
            if (isset($relation) && is_array($relation)) {
                foreach ($relation as $key => $value) {
                    $this->attributes[$key] = $value;
                }
            } else {
                $this->attributes[$relation_key] = $relation;
            }
        }

        return $this;
    }

    /**
     * Append the relations attributes.
     *
     * @return static
     */
    public function combine(array $keys = [])
    {
        return $this->merge($keys)->concat($keys);
    }

    /**
     * Append the relations attributes.
     *
     * @return static
     */
    public function concat(array $keys = [])
    {
        if (Arr::isAssoc($keys)) {
            $keys = array_fill_keys(array_keys($keys), 1);
        } else {
            $keys = array_fill_keys($keys, 1);
        }

        return $this->concatRelations($this->getRelations(), $keys);
    }

    /**
     * @return static
     */
    protected function concatRelations(array $relations, array $keys = [])
    {
        foreach ($relations as $relation_key => $relation) {
            if (isset($relation)) {
                $relation = $relation instanceof Collection ? $relation->first() : $relation;
                if (isset($relation)) {
                    foreach ($relation->attributesToArray() as $key => $value) {
                        if (isset($keys[$relation_key . '.' . $key])) {
                            $this->attributes[$key] = $value;
                        }
                    }

                    $this->concatRelations($relation->getRelations());
                }
            }

            $this->unsetRelation($relation_key);
        }

        return $this;
    }

    /**
     * @return static
     */
    protected function flattenRelations(array $relations, array $except = [], array $exclude = [])
    {
        foreach ($relations as $relation_key => $relation) {
            if (isset($relation)) {
                if (!in_array($relation_key, $except)) {
                    $relation = $relation instanceof Collection ? $relation->first() : $relation;
                    if (isset($relation)) {
                        foreach ($relation->attributesToArray() as $key => $value) {
                            if (!isset($exclude[$relation_key . '.' . $key])) {
                                $this->attributes[$key] = $value;
                            }
                        }

                        $this->flattenRelations($relation->getRelations(), $except, $exclude);
                    }
                }

                $this->unsetRelation($relation_key);
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    protected function flatRelations(array $relations, array $except = [], array $exclude = [])
    {
        $relations = $this->getAllRelations($relations);
        $relations = array_reverse($relations, true);
        $values = [];

        foreach ($relations as $relation_key => $relation) {
            if (isset($relation)) {
                if (!in_array($relation_key, $except)) {
                    $relation = $relation instanceof Collection ? $relation->first() : $relation;
                    if (isset($relation)) {
                        foreach ($relation->attributesToArray() as $key => $value) {
                            if (!isset($exclude[$relation_key . '.' . $key])) {
                                $values[$key] = $value;
                            }
                        }
                    }
                }
            }
        }

        $this->attributes = array_merge($values, $this->attributes);

        return $this;
    }

    protected function getAllRelations(array $relations): array
    {
        foreach ($relations as $relation_key => $relation) {
            if (isset($relation)) {
                $relation = $relation instanceof Collection ? $relation->first() : $relation;
                if (isset($relation)) {
                    array_merge($relations, $this->getAllRelations($relation->getRelations()));
                }
            }

            $this->unsetRelation($relation_key);
        }

        return $relations;
    }
}
