<?php

namespace Karla\Traits;

trait Builder
{
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

    protected function update($id, array $data, $column = 'id')
    {
        return $this->table()
            ->where($column, $id)
            ->update($data);
    }

    protected function insert(array $data)
    {
        return $this->table()->insert($data);
    }

    protected function insertGetId(array $data, $sequence = null)
    {
        return $this->table()->insertGetId($data, $sequence);
    }

    protected function getRows($data = null, $order = ['created_at' => 'desc'])
    {
        $data = $data ?: $this->all();

        return $this->table()
            ->filter($data)
            ->ordering($data, $order)
            ->paging();
    }
}
