<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Support\Facades\DB;

trait Builder
{
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
     * @param  mixed  $sql
     * @return bool|int
     */
    public function statement($sql)
    {
        $prefix = DB::getTablePrefix();
        $sql = \str_replace('#__', $prefix, $sql);

        return $this->pdo()->exec($sql);
    }

    /**
     * RAW database query.
     *
     * @param  string  $column
     */
    protected function raw($column): \Illuminate\Contracts\Database\Query\Expression
    {
        return DB::raw($column);
    }
}
