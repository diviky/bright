<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Batch
{
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
     * @var Model
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
     * @var Builder
     */
    protected $builder;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     */
    public function __construct($builder)
    {
        $this->model = $builder->getModel();
        $this->builder = $builder->getQuery();
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

    public function make(array $attributes = []): ?Model
    {
        $model = $this->model->make($attributes);
        if ($model->fireEvent('creating') === false) {
            return null;
        }

        if ($model->usesTimestamps()) {
            $model->updateTimestamps();
        }

        return $model;
    }

    public function commit(): bool
    {
        if ($this->bulk && is_resource($this->stream)) {
            return $this->commitBulk();
        }

        return $this->commitInsert();
    }

    public function commitInsert(): bool
    {
        $result = true;
        $async = $this->builder->getAsync();
        foreach (array_chunk($this->values, $this->limit) as $values) {
            $models = [];
            $attributes = [];

            foreach ($values as $value) {
                $model = $this->make($value);

                if (is_null($model)) {
                    continue;
                }

                $attributes[] = $model->getAttributes();
                $models[] = $model;
            }

            if (!$this->model->async($async)->es(false)->insert($attributes)) {
                return false;
            }

            $this->executeModelEvent($models);
            unset($models, $attributes);
        }

        return $result;
    }

    public function commitBulk(): bool
    {
        $models = [];
        foreach ($this->values as $attributes) {
            $model = $this->make($attributes);

            if (is_null($model)) {
                continue;
            }

            $attributes = $model->getAttributes();

            \fwrite($this->stream, \implode('[F]', $attributes) . '[L]');

            if ($this->count == 0) {
                $this->fields = array_keys($attributes);
            }

            $this->count++;

            $models[] = $model;
        }

        // @psalm-suppress
        fclose($this->stream);

        $sql = "LOAD DATA LOCAL INFILE '" . $this->path . "'";
        $sql .= ' INTO TABLE #from#';
        $sql .= " FIELDS TERMINATED  BY '[F]' LINES TERMINATED BY '[L]'";
        $sql .= ' (' . \implode(',', $this->fields) . ') ';

        if ($this->builder->statement($sql)) {
            $this->executeModelEvent($models);
            unset($models);

            return true;
        }

        return false;
    }

    protected function executeModelEvent(array $models): void
    {
        foreach ($models as $model) {
            $model->fireEvent('created', false);
        }
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
