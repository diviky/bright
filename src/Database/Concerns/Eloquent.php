<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Eloquent
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $eloquent;

    /**
     * Get the value of eloquent.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getEloquent()
    {
        return $this->eloquent;
    }

    /**
     * Set the value of eloquent.
     *
     * @return self
     */
    public function setEloquent(Builder $eloquent)
    {
        $this->eloquent = $eloquent;

        return $this;
    }

    public function hasEloquent(): bool
    {
        return isset($this->eloquent) ? true : false;
    }
}
