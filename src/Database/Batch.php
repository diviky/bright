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
     * @var array
     */
    protected $fields = [];

    protected int $count = 0;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
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
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->builder = $model->getQuery();
        $this->values = [];
    }

    /**
     * @param bool $bulk
     */
    public function bulk($bulk): self
    {
        $this->bulk = $bulk;

        if ($bulk) {
            $this->generateFilePath();
        }

        return $this;
    }

    public function add(array $values = []): self
    {
        $values = $this->model->make($values)->getAttributes();
        $values = $this->builder->insertEvent($values);

        if ($this->bulk && $this->stream) {
            \fwrite($this->stream, \implode('[F]', $values) . '[L]');
        } else {
            $this->values[] = $values;
        }

        if (0 == $this->count) {
            $this->fields = array_keys($values);
        }

        ++$this->count;

        return $this;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        if ($this->bulk && is_resource($this->stream)) {
            // @psalm-suppress
            fclose($this->stream);

            $sql = "LOAD DATA LOCAL INFILE '" . $this->path . "'";
            $sql .= ' INTO TABLE #from#';
            $sql .= " FIELDS TERMINATED  BY '[F]' LINES TERMINATED BY '[L]'";
            $sql .= ' (' . \implode(',', $this->fields) . ') ';

            return $this->builder->statement($sql);
        }

        $result = true;
        foreach (array_chunk($this->values, $this->limit) as $values) {
            if (!$this->model->insert($values)) {
                return false;
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
