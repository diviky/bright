<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

class Batch
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $fields = [];

    protected int $count = 0;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Bulk insert linit.
     */
    protected int $limit = 1000;

    /**
     * @var bool
     */
    protected $bulk = false;

    /**
     * @var resource
     *
     * @psalm-suppress
     */
    protected $stream;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \Illuminate\Database\Query\Builder
     */
    protected $builder;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    public function __construct($builder)
    {
        $this->model = $builder->getModel();
        $this->builder = $builder->getQuery();
        $this->attributes = [];
    }

    public function bulk(bool $bulk = true): self
    {
        $this->bulk = $bulk;

        if ($bulk) {
            $this->generateFilePath();
        }

        return $this;
    }

    public function add(array $values = []): self
    {
        $this->values[] = $values;

        return $this;
    }

    public function format(array $attributes = []): self
    {
        $attributes = $this->make($attributes);

        if (is_null($attributes)) {
            return $this;
        }

        if ($this->bulk && $this->stream) {
            \fwrite($this->stream, \implode('[F]', $attributes) . '[L]');
        } else {
            $this->attributes[] = $attributes;
        }

        if (0 == $this->count) {
            $this->fields = array_keys($attributes);
        }

        ++$this->count;

        return $this;
    }

    public function make(array $attributes = []): ?array
    {
        $model = $this->model->make($attributes);
        if (false === $model->fireEvent('creating')) {
            return null;
        }

        if ($model->usesTimestamps()) {
            $model->updateTimestamps();
        }

        $attributes = $model->getAttributes();

        $model->fireEvent('created', false);

        return $attributes;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        foreach ($this->values as $values) {
            $this->format($values);
        }

        if ($this->bulk && is_resource($this->stream)) {
            // @psalm-suppress
            fclose($this->stream);

            $sql = "LOAD DATA LOCAL INFILE '" . $this->path . "'";
            $sql .= ' INTO TABLE #from#';
            $sql .= " FIELDS TERMINATED  BY '[F]' LINES TERMINATED BY '[L]'";
            $sql .= ' (' . \implode(',', $this->fields) . ') ';

            if ($this->builder->statement($sql)) {
                return true;
            }

            return false;
        }

        $result = true;
        $async = $this->builder->getAsync();

        if ($async) {
            foreach (array_chunk($this->attributes, $this->limit) as $attributes) {
                if (!$this->model->async($async)->es(false)->insert($attributes)) {
                    return false;
                }
            }
        } else {
            foreach (array_chunk($this->attributes, $this->limit) as $attributes) {
                if (!$this->model->es(false)->insert($attributes)) {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * Generate the filename.
     */
    protected function generateFilePath(): string
    {
        $this->path = \sys_get_temp_dir() . '/' . \uniqid() . '.csv';
        $this->stream = fopen($this->path, 'w');

        return $this->path;
    }
}
