<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

trait Raw
{
    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string $sql
     */
    public function groupByRaw($sql, array $bindings = [])
    {
        if (is_string($sql)) {
            $sql = array_filter(explode(', ', $sql), 'trim');
        }

        foreach ($sql as &$exp) {
            if (\is_string($exp) && false !== \strpos($exp, '.')) {
                if (false !== \strpos($exp, '(')) {
                    $exp = $this->wrap($exp);
                } else {
                    $exp = $this->grammar->wrap($exp);
                }
            }
        }

        $sql = \implode(', ', $sql);

        return parent::groupByRaw($sql, $bindings);
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string $sql
     * @param mixed  $bindings
     * @param string $boolean
     *
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        if (false !== \strpos($sql, '(')) {
            $sql = $this->wrap(\trim($sql));
        }

        return parent::whereRaw($sql, $bindings, $boolean);
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param array|string $expression
     *
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        if (\is_array($expression)) {
            foreach ($expression as &$exp) {
                if (\is_string($exp) && false !== \strpos($exp, '.')) {
                    if (false !== \strpos($exp, '(')) {
                        $exp = $this->wrap(\trim($exp));
                    } else {
                        $exp = $this->grammar->wrap(\trim($exp));
                    }
                }
            }

            $expression = \implode(', ', $expression);
        }

        return parent::selectRaw($expression, $bindings);
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereBetweenRaw($column, array $values, $boolean = 'and', $not = false): self
    {
        $column = $this->raw($this->wrap($column));

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    /**
     * Update records in the database.
     */
    public function updateRaw(array $values): int
    {
        foreach ($values as $key => $value) {
            if (':' == \substr((string) $value, 0, 1)) {
                $values[$key] = $this->raw(\substr((string) $value, 1));
            }
        }

        return parent::update($values);
    }

    /**
     * Wrap the query string.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrap($value)
    {
        if (\preg_match_all('/([^\W]+)\.([^\W]+)?/', $value, $matches)) {
            foreach ($matches[0] as $match) {
                $exp = $this->grammar->wrap(\trim($match));
                $value = \str_replace($match, $exp, $value);
            }
        }

        return $value;
    }

    /**
     * Wrap the query string.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrapColumn($value)
    {
        if (\preg_match('/\((.+)\)/', $value, $matches)) {
            if ($matches[1]) {
                $column = $matches[1];
                if (false !== \strpos($column, '(')) {
                    $column = $this->wrapColumn($column);
                } else {
                    $exps = \explode(', ', $column);
                    foreach ($exps as &$exp) {
                        if ("'" != \substr($exp, 0, 1)) {
                            $exp = $this->grammar->wrap(\trim($exp));
                        } else {
                            $exp = \str_replace('`', '', $exp);
                        }
                    }

                    $column = \implode(', ', $exps);
                }

                $value = \str_replace($matches[0], '(' . $column . ')', $value);
            }
        }

        return $value;
    }
}
