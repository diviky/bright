<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Illuminate\Support\Arr;

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
        $relations  = $this->getRelations();

        foreach ($relations as $relation_key => $relation) {
            if (isset($relation) && !in_array($relation_key, $except)) {
                $attributes = $relation->getAttributes();
                foreach ($attributes as $key => $value) {
                    //$this->attributes[$key] = $value;
                    $this->setAttribute($key, $value);
                }

                $this->unsetRelation($relation_key);
            }
        }

        return $this;
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
}
