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

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $val = func_num_args() === 2 ? $operator : $value;
        if (is_array($val)) {
            return parent::whereIn($column, $val);
        }

        return parent::where($column, $operator, $value, $boolean);
    }
}
