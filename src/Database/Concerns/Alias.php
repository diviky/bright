<?php

namespace Diviky\Bright\Database\Concerns;

trait Alias
{
    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = [];
        $this->bindings['select'] = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                $this->selectSub($column, $as);
            } else {
                if (is_string($column)) {
                    $this->columns[] = $this->addAliasToColumn($column);
                } else {
                    $this->columns[] = $column;
                }
            }
        }

        return $this;
    }

    protected function addAliasToColumn(string $column): string
    {
        if (str_contains($column, ' as ')) {
            return $column;
        }

        $alias = $this->getAlias($this->from);

        return $alias . $column;
    }

    protected function getAlias(string $table): string
    {
        $from = \preg_split('/ as /i', $this->getExpressionValue($table));

        $alias = \count($from) > 1 ? last($from) : $from[0];

        return $alias . '.';
    }
}
