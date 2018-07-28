<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Facades\DB;

trait Raw
{
    public function groupByRaw($sql, array $bindings = [])
    {
        if (is_array($sql)) {
            $sql = implode(', ', $sql);
        }

        $this->groupBy(DB::raw($sql));

        if ($bindings) {
            $this->setBindings($bindings, 'group');
            $this->addBinding($bindings, 'group');
        }

        return $this;
    }

    public function selectRaw($expression, array $bindings = [])
    {
        if (is_array($expression)) {
            $expression = implode(', ', $expression);
        }

        return parent::selectRaw($expression, $bindings);
    }

    public function whereBetweenRaw($column, array $values, $boolean = 'and', $not = false)
    {
        $column = DB::raw($column);

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    public function updateRaw(array $values)
    {
        foreach ($values as $key => $value) {
            if (':' == substr($value, 0, 1)) {
                $values[$key] = DB::raw(substr($value, 1));
            }
        }

        return parent::update($values);
    }
}
