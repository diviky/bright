<?php

namespace Karla\Database;

use Illuminate\Support\Carbon;
use Karla\Traits\CapsuleManager;

class Meta
{
    use CapsuleManager;

    protected $fields   = [];
    protected $table    = 'desk_meta';
    protected $relation = 'desk_meta_values';

    public function updateOrInsert($key, $value = null)
    {
        if (is_array($key)) {
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

    public static function instance()
    {
        return new self();
    }

    public function update($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $val) {
                $this->update($k, $val);
            }

            return true;
        }

        $field = $this->getField($key);
        $id    = $field['id'];

        if (empty($id)) {
            return false;
        }

        $time = new Carbon();

        $values = [
            'meta_value' => $value,
            'updated_at' => $time,
        ];

        return $this->db->table($this->relation)
            ->where('option_id', $id)
            ->update($values);
    }

    public function insert($key, $value)
    {
        $field = $this->getField($key);

        $id = $field['id'];

        if (empty($id)) {
            return false;
        }

        $time = new Carbon();

        $values = [
            'option_id'  => $id,
            'meta_value' => $value,
            'created_at' => $time,
            'updated_at' => $time,
        ];

        return $this->db->table($this->relation)->insert($values);
    }

    public function find()
    {
        $field = $this->getField($key);

        $row = $this->db->table($this->relation)
            ->where('option_id', $field['id'])
            ->first();

        // Is value exists
        if (!is_null($row) && isset($row->meta_value)) {
            return $row->meta_value;
        }

        if ($default) {
            return $default;
        }

        return $field['default_value'];
    }

    public function value($key, $default = null)
    {
        $field = $this->getField($key);

        $row = $this->db->table($this->relation)
            ->where('option_id', $field['id'])
            ->first();

        // Is value exists
        if (!is_null($row) && isset($row->meta_value)) {
            return $row->meta_value;
        }

        if ($default) {
            return $default;
        }

        return $field['default_value'];
    }

    public function exists($key)
    {
        $field = $this->getField($key);

        return $this->db->table($this->relation)
            ->where('option_id', $field['id'])
            ->exists();
    }

    protected function getField($key)
    {
        $fields = $this->getFields();

        return $fields[$key];
    }

    protected function getFields()
    {
        if (!is_null($this->fields)) {
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

    /**
     * Set the value of table.
     *
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
     * @return self
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }
}
