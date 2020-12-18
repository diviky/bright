<?php

namespace Diviky\Bright\Traits;

use Diviky\Bright\Database\Bright;
use Illuminate\Support\Facades\DB;

trait Builder
{
    public function updateOrInsert(array $attributes, array $values = [])
    {
        return $this->table()->updateOrInsert($attributes, $values);
    }

    public function pdo()
    {
        return DB::connection()->getPdo();
    }

    public function statement($sql, array $bindings = [])
    {
        $prefix = DB::getTablePrefix();
        $sql    = \str_replace('#__', $prefix, $sql);

        return $this->pdo()->exec($sql);
    }

    public function bright()
    {
        return new Bright();
    }

    protected function table($table = null, $timestamps = true)
    {
        $table = $table ?: $this->table;

        $database = $this->db->table($table);
        $database->timestamps($timestamps);

        return $database;
    }

    protected function softDelete($id)
    {
        return $this->table()->softDelete($id);
    }

    protected function find($id)
    {
        return $this->table()->find($id);
    }

    protected function update($id, array $values, $column = 'id')
    {
        return $this->table()
            ->where($column, $id)
            ->update($values);
    }

    protected function insert(array $values)
    {
        return $this->table()->insert($values);
    }

    protected function insertGetId(array $values, $sequence = null)
    {
        return $this->table()->insertGetId($values, $sequence);
    }

    protected function getRows($values = null, $order = ['created_at' => 'desc'])
    {
        $values = $values ?: $this->all();

        return $this->table()
            ->filter($values)
            ->ordering($values, $order)
            ->paging();
    }

    protected function updateWithMessage($id, $values, $name = null)
    {
        $result = $this->update($id, $values);

        return $this->updated($result, $name);
    }

    protected function insertWithMessage($values, $name = null)
    {
        $result = $this->insert($values);

        return $this->inserted($result, $name);
    }

    protected function raw($column)
    {
        return DB::raw($column);
    }
}
