<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Concerns\CapsuleManager;
use Illuminate\Support\Carbon;

class Meta
{
    use CapsuleManager;

    /**
     * Fields to select.
     *
     * @var null|array
     */
    protected $fields;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'meta';

    /**
     * Relation table.
     *
     * @var string
     */
    protected $relation = 'meta_values';

    /**
     * Update the record exists else insert.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return bool|int
     */
    public function updateOrInsert($key, $value = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $val) {
                $this->updateOrInsert($k, $val);
            }

            return true;
        }

        if ($this->exists($key)) {
            return $this->update($key, $value);
        }

        return $this->insert($key, $value);
    }

    public static function instance(): self
    {
        return new self;
    }

    /**
     * Update the records.
     *
     * @param  mixed  $key
     * @param  mixed  $value
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

        $field = $this->getField($key);
        $id = $field['id'] ?? null;

        if (empty($id)) {
            return false;
        }

        $time = new Carbon;

        $values = [
            'meta_value' => $value,
            'updated_at' => $time,
        ];

        return $this->db->table($this->relation)
            ->where('option_id', $id)
            ->update($values);
    }

    /**
     * Insert data into table.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function insert($key, $value)
    {
        $field = $this->getField($key);

        $id = $field['id'] ?? null;

        if (empty($id)) {
            return false;
        }

        $time = new Carbon;

        $values = [
            'option_id' => $id,
            'meta_value' => $value,
            'created_at' => $time,
            'updated_at' => $time,
        ];

        return $this->db->table($this->relation)->insert($values);
    }

    /**
     * Get the value from store.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function value($key, $default = null)
    {
        $field = $this->getField($key);

        $row = $this->db->table($this->relation)
            ->where('option_id', $field['id'])
            ->first();

        // Is value exists
        if (!\is_null($row) && isset($row->meta_value)) {
            return $row->meta_value;
        }

        if ($default) {
            return $default;
        }

        return $field['default_value'] ?? null;
    }

    /**
     * Check the values exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function exists($key)
    {
        $field = $this->getField($key);

        return $this->db->table($this->relation)
            ->where('option_id', $field['id'])
            ->exists();
    }

    /**
     * Set the value of table.
     *
     * @param  mixed  $table
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set the value of relation.
     *
     * @param  mixed  $relation
     * @return self
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get value from field.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getField($key)
    {
        $fields = $this->getFields();

        return $fields[$key] ?? [];
    }

    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array<array-key, array|mixed>
     */
    protected function getFields(): array
    {
        if (!\is_null($this->fields)) {
            return $this->fields;
        }

        $rows = $this->db->table($this->table)->get();

        $fields = [];
        foreach ($rows as $row) {
            $fields[$row->colum_name] = (array) $row;
        }

        $this->fields = $fields;

        return $fields;
    }
}
