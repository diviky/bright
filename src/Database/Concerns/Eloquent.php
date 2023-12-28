<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Eloquent
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Get the value of eloquent.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the value of eloquent.
     *
     * @return self
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the value of eloquent.
     *
     * @return self
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    public function hasModel(): bool
    {
        return ($this->model instanceof Model) ? true : false;
    }
}
