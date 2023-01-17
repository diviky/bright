<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Collection extends EloquentCollection
{
    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function flats(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->flat($except, $exclude);
        });

        return $this;
    }

    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function flattens(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->flatten($except, $exclude);
        });

        return $this;
    }

    /**
     * Merge the relations attributes.
     *
     * @return $this
     */
    public function collapses(array $except = [], array $exclude = [])
    {
        $this->transform(function ($row) use ($except, $exclude) {
            return $row->collapse($except, $exclude);
        });

        return $this;
    }

    /**
     * Returns only the models from the collection with the specified keys.
     *
     * @return $this
     */
    public function few(array $keys)
    {
        $this->transform(function ($row) use ($keys) {
            return $row->some($keys);
        });

        return $this;
    }
}
