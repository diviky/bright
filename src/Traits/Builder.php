<?php

namespace Diviky\Bright\Traits;

use Diviky\Bright\Database\Bright;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait Builder
{
    /**
     * Insert or update a record matching the attributes, and fill it with values.
     *
     * @return bool
     */
    public function updateOrInsert(array $attributes, array $values = [])
    {
        return $this->table()->updateOrInsert($attributes, $values);
    }

    /**
     * Get the PDO connection to use for a select query.
     *
     * @return \PDO
     */
    public function pdo()
    {
        return DB::connection()->getPdo();
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param mixed  $sql
     *
     * @return bool|int
     */
    public function statement($sql)
    {
        $prefix = DB::getTablePrefix();
        $sql    = \str_replace('#__', $prefix, $sql);

        return $this->pdo()->exec($sql);
    }

    public function bright(): Bright
    {
        return new Bright();
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $table
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table = null)
    {
        $table = $table ?: $this->table;

        return $this->db->table($table);
    }

    /**
     * Soft delete rows.
     *
     * @param array|int|string $id
     *
     * @return bool
     */
    protected function softDelete($id)
    {
        return $this->table()->softDelete($id);
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param int|string $id
     *
     * @return mixed|static
     */
    protected function find($id)
    {
        return $this->table()->find($id);
    }

    /**
     * Update records in the database.
     *
     * @param mixed $id
     * @param mixed $column
     *
     * @return int
     */
    protected function update($id, array $values, $column = 'id')
    {
        return $this->table()
            ->where($column, $id)
            ->update($values);
    }

    /**
     * Insert new records into the database.
     *
     * @return bool
     */
    protected function insert(array $values)
    {
        return $this->table()->insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param null|string $sequence
     *
     * @deprecated deprecated since version 2.0
     *
     * @return int
     */
    protected function insertGetId(array $values, $sequence = null)
    {
        return $this->table()->insertGetId($values, $sequence);
    }

    /**
     * Get the records from table.
     *
     * @param null|array $values
     * @param array      $order
     *
     * @deprecated deprecated since version 2.0
     *
     * @return Collection
     */
    protected function getRows($values = null, $order = ['created_at' => 'desc'])
    {
        $values = $values ?: $this->request()->all();

        return $this->table()
            ->filter($values)
            ->ordering($values, $order)
            ->paging();
    }

    /**
     * RAW database query.
     *
     * @param string $column
     */
    protected function raw($column): \Illuminate\Database\Query\Expression
    {
        return DB::raw($column);
    }
}
