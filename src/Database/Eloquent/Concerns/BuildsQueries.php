<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait BuildsQueries
{
    /**
     * @param  string|array  $attributes
     * @return self
     */
    public function whereLike($attributes, string $searchTerm)
    {
        $this->query->whereLike($attributes, $searchTerm);

        return $this;
    }
}
