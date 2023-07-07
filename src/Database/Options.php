<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Concerns\CapsuleManager;
use Diviky\Bright\Models\Options as ModelsOptions;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class Options
{
    use CapsuleManager;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'app_options';

    /**
     * Values to save.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Values for update.
     *
     * @var array
     */
    protected $updates = [];

    /**
     * Extra data to be saved.
     *
     * @var array
     */
    protected $extra = [];

    /**
     * Conditions.
     *
     * @var array
     */
    protected $where = [];

    /**
     * @param string $table
     * @param string $group
     */
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

    /**
     * Instance.
     *
     * @param string $table
     */
    public static function instance($table = null): self
    {
        return new self($table);
    }

    /**
     * Update the record exists else insert.
     *
     * @param mixed  $key
     * @param mixed  $value
     * @param string $type
     *
     * @return bool|int
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
     * Update the records.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool|int
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

        $values = [
            'option_value' => $value,
            'option_type' => $type,
            'updated_at' => now(),
        ];

        $values = \array_merge($values, $this->updates);
        $values = \array_merge($values, $this->extra);

        return $this->table()
            ->where('option_name', $key)
            ->update($values);
    }

    /**
     * Insert data into table.
     *
     * @param string      $key
     * @param mixed       $value
     * @param null|string $type
     *
     * @return bool|int
     */
    public function insert($key, $value, $type = null)
    {
        $type = $this->identifyType($value, $type);

        if ('json' == $type) {
            $value = \json_encode($value);
        }

        $values = [
            'option_name' => $key,
            'option_value' => $value,
            'option_type' => $type,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $values = \array_merge($values, $this->values);
        $values = \array_merge($values, $this->extra);

        return $this->table()->insert($values);
    }

    /**
     * Get the record from table.
     */
    public function first(): object
    {
        $rows = $this->table()->get();

        $values = [];
        foreach ($rows as $row) {
            $values[$row->option_name] = $this->formatValue($row->option_value, $row->option_type);
        }

        return (object) $values;
    }

    /**
     * Convert to collection.
     */
    public function collect(): Collection
    {
        return collect((array) $this->first());
    }

    /**
     * Add where condition.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function where($key, $value = null): self
    {
        if (!\is_array($key)) {
            $key = [$key => $value];
        }

        $this->where = \array_merge($this->where, $key);

        return $this;
    }

    /**
     * Get the values from table.
     *
     * @return Collection
     */
    public function find()
    {
        $rows = $this->table()->get();

        $rows->transform(function (object $row) {
            $row->value = $this->formatValue($row->option_value, $row->option_type);

            return $row;
        });

        return $rows;
    }

    /**
     * Get the values and group by name.
     *
     * @param string $name
     *
     * @return Collection
     */
    public function keyBy($name = 'option_name')
    {
        $rows = $this->find();

        return $rows->keyBy($name);
    }

    /**
     * Get the value from store.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
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

    /**
     * Check the values exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return $this->table()
            ->where('option_name', $key)
            ->exists();
    }

    /**
     * Get the table.
     *
     * @param string $name
     *
     * @return Builder
     */
    public function table($name = null)
    {
        $name = $name ?: $this->table;

        return ModelsOptions::from($name)
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
     * @param array $values
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
     * @param array $values
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
     * @param array $values
     *
     * @return self
     */
    public function extra($values)
    {
        $this->extra = \array_merge($this->extra, $values);

        return $this;
    }

    /**
     * Identify the column type.
     *
     * @param mixed       $value
     * @param null|string $type
     *
     * @return mixed
     */
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

    /**
     * Format the values.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
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
