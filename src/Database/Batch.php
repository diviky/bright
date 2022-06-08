<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

class Batch
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var bool
     */
    protected $bulk = false;

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->values = [];
    }

    /**
     * @param bool $bulk
     */
    public function bulk($bulk): self
    {
        $this->bulk = $bulk;

        return $this;
    }

    public function add(array $values = []): self
    {
        $this->values[] = $this->model->make($values)->getAttributes();

        return $this;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->model->insert($this->values);
    }
}
