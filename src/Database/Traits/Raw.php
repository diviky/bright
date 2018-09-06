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

    public function whereRaw($column, $binds = [], $boolean = 'and')
    {
        if (false !== strpos($column, '(')) {
            $column = $this->wrap(trim($column));
        }

        return parent::whereRaw($column, $binds, $boolean);
    }

    protected function wrap($value)
    {
        if (preg_match_all('/([^\W]+)\.([^\W]+)?/', $value, $matches)) {
            foreach ($matches[0] as $match) {
                $exp   = $this->grammar->wrap(trim($match));
                $value = str_replace($match, $exp, $value);
            }
        }

        return $value;
    }

    public function selectRaw($expression, array $bindings = [])
    {
        if (is_array($expression)) {
            foreach ($expression as &$exp) {
                if (is_string($exp) && false !== strpos($exp, '.')) {
                    if (false !== strpos($exp, '(')) {
                        $exp = $this->wrap(trim($exp));
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
                $column = $matches[1];
                if (is_string($column)) {
                    if (false !== strpos($column, '(')) {
                        $column = $this->wrapColumn($column);
                    } else {
                        $exps = explode(', ', $column);
                        foreach ($exps as &$exp) {
                            if ("'" != substr($exp, 0, 1)) {
                                $exp = $this->grammar->wrap(trim($exp));
                            } else {
                                $exp = str_replace('`', '', $exp);
                            }
                        }

                        $column = implode(', ', $exps);
                    }

                    $value = str_replace($matches[0], '(' . $column . ')', $value);
                }
            }
        }

        return $value;
    }

    public function whereBetweenRaw($column, array $values, $boolean = 'and', $not = false)
    {
        $column = $this->raw($this->wrap($column));

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
