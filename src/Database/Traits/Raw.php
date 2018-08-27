<?php

namespace Karla\Database\Traits;

trait Raw
{
    public function groupByRaw($sql, array $bindings = [])
    {
        if (is_array($sql)) {
            $sql = implode(', ', $sql);
        }

        $this->groupBy($this->raw($sql));

        if ($bindings) {
            $this->setBindings($bindings, 'group');
            $this->addBinding($bindings, 'group');
        }

        return $this;
    }

    public function selectRaw($expression, array $bindings = [])
    {
        if (is_array($expression)) {
            foreach ($expression as &$exp) {
                if (is_string($exp) && strpos($exp, '.') !== false) {
                    if (false !== strpos($exp, '(')) {
                        $exp = $this->wrapColumn(trim($exp));
                    } else {
                        $exp = $this->grammar->wrap(trim($exp));
                    }
                }
            }

            $expression = implode(', ', $expression);
        }

        return parent::selectRaw($expression, $bindings);
    }

    protected function wrapColumn($value)
    {
        if (preg_match('/\((.+)\)/', $value, $matches)) {
            if ($matches[1]) {
                $columns = explode(',', $matches[1]);
                foreach ($columns as &$column) {
                    if (is_string($column)) {
                        if (false !== strpos($column, '(')) {
                            if (false !== strpos($column, '.')) {
                                $column = $this->wrapColumn($column);
                            }
                        } else {
                            $column = $this->grammar->wrap(trim($column));
                        }
                    }
                }
                $columns = implode(', ', $columns);
                $value   = str_replace($matches[0], '(' . $columns . ')', $value);
            }
        }

        return $value;
    }

    public function whereBetweenRaw($column, array $values, $boolean = 'and', $not = false)
    {
        $column = $this->raw($column);

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    public function updateRaw(array $values)
    {
        foreach ($values as $key => $value) {
            if (':' == substr($value, 0, 1)) {
                $values[$key] = $this->raw(substr($value, 1));
            }
        }

        return parent::update($values);
    }
}
