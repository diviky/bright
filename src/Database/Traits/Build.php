<?php

namespace Diviky\Bright\Database\Traits;

trait Build
{
    /**
     * Add a where between statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     * @param array|string                                 $values
     * @param string                                       $boolean
     * @param bool                                         $not
     *
     * @return $this
     */
    public function whereDateBetween($column, $values, $boolean = 'and', $not = false): static
    {
        if (!\is_array($values)) {
            return parent::whereDate((string) $column, $values);
        }

        $column = $this->raw('DATE(' . $this->grammar->wrap($column) . ')');

        return parent::whereBetween($column, $values, $boolean, $not);
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param array|\Closure|string $column
     * @param mixed                 $operator
     * @param mixed                 $value
     * @param string                $boolean
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $val = 2 === \func_num_args() ? $operator : $value;
        if (\is_array($val)) {
            return parent::whereIn($column, $val);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Delete a record from the database.
     *
     * @param mixed $id
     *
     * @return array|bool|int
     */
    public function deletes()
    {
        $query = $this->toQuery();

        return $this->statement('DELETE ' . \substr($query, 6));
    }
}
