<?php

namespace Karla\Database\Traits;

trait Build
{
    public function whereDateBetween($column, $values, $boolean = 'and', $not = false)
    {
        if (!is_array($values)) {
            return parent::whereDate($column, $values);
        }

        $column = $this->raw('DATE(' . $this->grammar->wrap($column) . ')');

        return parent::whereBetween($column, $values, $boolean, $not);
    }
}
