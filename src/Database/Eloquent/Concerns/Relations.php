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
     * @param array $except
     *
     * @return static
     */
    public function flatten($except = [])
    {
        return $this->flattenRelations($this->getRelations(), $except);
    }

    /**
     * Merge the relations attributes.
     *
     * @param array $except
     *
     * @return static
     */
    public function collapse($except = [])
    {
        return $this->flatten($except);
    }

    /**
     * Remove attributes from model attributes.
     *
     * @param array $keys
     *
     * @return static
     */
    public function except($keys)
    {
        $this->attributes = Arr::except($this->attributes, $keys);

        return $this;
    }

    /**
     * Returns only the models from the collection with the specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function some($keys)
    {
        $this->attributes = Arr::only($this->attributes, $keys);

        return $this;
    }

    /**
     * Merge keys into the attributes.
     *
     * @param array $keys
     *
     * @return static
     */
    public function merge($keys = [])
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
     * @param array $keys
     *
     * @return static
     */
    public function combine($keys = [])
    {
        return $this->merge($keys)->concat($keys);
    }

    /**
     * Append the relations attributes.
     *
     * @param array $keys
     *
     * @return static
     */
    public function concat($keys = [])
    {
        if (Arr::isAssoc($keys)) {
            $keys = array_fill_keys(array_keys($keys), 1);
        } else {
            $keys = array_fill_keys($keys, 1);
        }

        return $this->concatRelations($this->getRelations(), $keys);
    }

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

    protected function flattenRelations(array $relations, array $except = [])
    {
        foreach ($relations as $relation_key => $relation) {
            if (isset($relation)) {
                if (!in_array($relation_key, $except)) {
                    $relation = $relation instanceof Collection ? $relation->first() : $relation;
                    if (isset($relation)) {
                        foreach ($relation->attributesToArray() as $key => $value) {
                            $this->attributes[$key] = $value;
                        }

                        $this->flattenRelations($relation->getRelations());
                    }
                }

                $this->unsetRelation($relation_key);
            }
        }

        return $this;
    }
}
