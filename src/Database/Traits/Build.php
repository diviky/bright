<?php

namespace Diviky\Bright\Database\Traits;

trait Build
{
    public function whereDateBetween($column, $values, $boolean = 'and', $not = false): static
    {
        if (!\is_array($values)) {
            return parent::whereDate($column, $values);
        }

        $column = $this->raw('DATE(' . $this->grammar->wrap($column) . ')');

        return parent::whereBetween($column, $values, $boolean, $not);
    }

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
     * @return int
     */
    public function deletes()
    {
        $query = $this->toQuery();

        return $this->statement('DELETE ' . \substr($query, 6));
    }
}
