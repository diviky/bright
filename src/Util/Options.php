<?php

namespace Karla\Util;

use Illuminate\Support\Carbon;
use Karla\Traits\CapsuleManager;

class Options
{
    use CapsuleManager;

    protected $table  = 'app_options';
    protected $values = [];
    protected $where  = [];

    public function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        }
    }

    public static function instance($table = null)
    {
        return new self($table);
    }

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

    public function update($key, $value = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $val) {
                $this->update($k, $val);
            }

            return true;
        }

        $time = new Carbon();

        $values = [
            'option_value' => $value,
            'updated_at'   => $time,
        ];

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

        return $this->table()->insert($values);
    }

    public function first()
    {
        $rows = $this->table()->get();

        $values = [];
        foreach ($rows as $row) {
            $values[$row->option_name] = $this->formatValue($row->option_value, $row->option_type);
        }

        return (object) $values;
    }

    public function collect()
    {
        return collect($this->first());
    }

    public function where($key, $value = null, $operator = '=')
    {
        if (!\is_array($key)) {
            $key = [$key => $value];
        }

        $this->where = \array_merge($this->where, $key);

        return $this;
    }

    public function find()
    {
        return $this->table()->get();
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

        return $this->db->table($name)->where($this->where);
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
        foreach ($values as $key => $value) {
            $this->where($key, $value);
        }

        $this->values = \array_merge($this->values, $values);

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
            $value = \json_decode($value);
        }

        if ('number' == $type) {
            $value = \intval($value);
        }

        return $value;
    }
}
