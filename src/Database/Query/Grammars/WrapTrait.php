<?php

namespace Karla\Database\Query\Grammars;

trait WrapTrait
{
    /**
     * Wrap a table in keyword identifiers.
     *
     * @param \Illuminate\Database\Query\Expression|string $table
     *
     * @return string
     */
    public function wrapTables($table)
    {
        if (!$this->isExpression($table)) {
            if (false !== strpos($table, '.')) {
                list($database, $table) = explode('.', $table);

                return $this->wrap($database).'.'.$this->wrap($this->tablePrefix.$table, true);
            }

            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }
}
