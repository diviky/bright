<?php

namespace Karla\Database;

use Illuminate\Database\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    /**
     * Wrap a table in keyword identifiers.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $table
     * @return string
     */
    public function wrapTable($table)
    {
        if (!$this->isExpression($table)) {
            if (strpos($table, '.') !== false) {
                list($database, $table) = explode('.', $table);
                return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $table, true);
            }

            return $this->wrap($this->tablePrefix . $table, true);
        }

        return $this->getValue($table);
    }
}
