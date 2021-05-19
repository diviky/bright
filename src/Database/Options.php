<?php

namespace Diviky\Bright\Database;

use Diviky\Bright\Traits\CapsuleManager;
use Illuminate\Support\Carbon;

class Options
{
    use CapsuleManager;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table   = 'app_options';

    protected $values  = [];

    protected $updates = [];

    protected $extra   = [];

    protected $where   = [];

    public function __construct($table = null, $group = null)
    {
        if ($table) {
            $this->table = $table;
        }

        if ($group) {
            $this->values['option_group'] = $group;
            $this->where(['option_group' => $group]);
        }
    }

    public static function instance($table = null): self
    {
        return new self($table);
    }

    /**
     * @param (int|string) $key
     */
    public function updateOrInsert($key, $value = null, $type = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $val) {
                $this->updateOrInsert($k, $val, $type);
            }

            return true;
        }

        if ($this->exists($key)) {
            return $this->update($key, $value);
        }

        return $this->insert($key, $value, $type);
    }

    /**
     * @param (int|string) $key
     */
    public function update($key, $value = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $val) {
                $this->update($k, $val);
            }

            return true;
        }

        $type = $this->identifyType($value);

        if ('json' == $type) {
            $value = \json_encode($value);
        }

        $time = new Carbon();

        $values = [
            'option_value' => $value,
            'option_type'  => $type,
            'updated_at'   => $time,
        ];

        $values = \array_merge($values, $this->updates);
        $values = \array_merge($values, $this->extra);

        return $this->table()
            ->where('option_name', $key)
            ->update($values);
    }

    public function insert($key, $value, $type = null)
    {
        $time = new Carbon();

        $type = $this->identifyType($value, $type);

        if ('json' == $type) {
            $value = \json_encode($value);
        }

        $values = [
            'option_name'  => $key,
            'option_value' => $value,
            'option_type'  => $type,
            'created_at'   => $time,
            'updated_at'   => $time,
        ];

        $values = \array_merge($values, $this->values);
        $values = \array_merge($values, $this->extra);

        return $this->table()->insert($values);
    }

    public function first(): \stdClass
    {
        $rows = $this->table()->get();

        $values = [];
        foreach ($rows as $row) {
            $values[$row->option_name] = $this->formatValue($row->option_value, $row->option_type);
        }

        return (object) $values;
    }

    public function collect(): \Illuminate\Support\Collection
    {
        return collect($this->first());
    }

    public function where(array $key, $value = null): static
    {
        if (!\is_array($key)) {
            $key = [$key => $value];
        }

        $this->where = \array_merge($this->where, $key);

        return $this;
    }

    public function find()
    {
        $rows = $this->table()->get();

        $rows->transform(function ($row) {
            $row->value = $this->formatValue($row->option_value, $row->option_type);

            return $row;
        });

        return $rows;
    }

    public function keyBy($name = 'option_name')
    {
        $rows = $this->find();

        return $rows->keyBy($name);
    }

    public function value($key, $default = null)
    {
        $row = $this->table()
            ->where('option_name', $key)
            ->first();

        // Is value exists
        if (!\is_null($row) && isset($row->option_value)) {
            return $this->formatValue($row->option_value, $row->option_type);
        }

        return $default;
    }

    public function exists($key)
    {
        return $this->table()
            ->where('option_name', $key)
            ->exists();
    }

    public function table($name = null)
    {
        $name = $name ?: $this->table;

        return $this->db
            ->table($name)
            ->where($this->where);
    }

    /**
     * Set the value of table.
     *
     * @param mixed $table
     *
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set the value of values.
     *
     * @param mixed $values
     *
     * @return self
     */
    public function values($values)
    {
        $this->values = \array_merge($this->values, $values);

        return $this;
    }

    /**
     * Set the value of values.
     *
     * @param mixed $values
     *
     * @return self
     */
    public function updates($values)
    {
        $this->updates = \array_merge($this->updates, $values);

        return $this;
    }

    /**
     * Set the value of values.
     *
     * @param mixed $values
     *
     * @return self
     */
    public function extra($values)
    {
        $this->extra = \array_merge($this->extra, $values);

        return $this;
    }

    protected function identifyType($value, $type = null)
    {
        if (!\is_null($type)) {
            return $type;
        }

        $type = 'string';
        if (\is_numeric($value)) {
            $type = 'number';
        }

        if (\is_bool($value)) {
            $type = 'number';
        }

        if (\is_array($value)) {
            $type = 'json';
        }

        return $type;
    }

    protected function formatValue($value, $type = 'string')
    {
        if ('json' == $type) {
            $value = \json_decode($value, true);
        }

        if ('number' == $type) {
            $value = (int) $value;
        }

        return $value;
    }
}
