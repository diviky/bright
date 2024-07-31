<?php

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Contracts\Database\Query\Expression as QueryExpression;

trait Builder
{
    /**
     * Set the alias for table.
     *
     * @param  string  $as
     */
    public function alias($as): self
    {
        $this->from = "{$this->from} as {$as}";

        return $this;
    }

    /**
     * Set the alias for table.
     *
     * @param  string  $as
     */
    public function as($as): self
    {
        return $this->alias($as);
    }

    public function insert(array $values)
    {
        $values = $this->insertEvent($values);

        return parent::insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  null|string  $sequence
     * @return int|string
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $values = $this->insertEvent($values);

        $id = parent::insertGetId($values[0], $sequence);

        if (empty($id)) {
            $id = $this->getLastId();
        }

        return $id;
    }

    public function delete($id = null)
    {
        $this->atomicEvent('delete');

        return parent::delete($id);
    }

    /**
     * Excecute the RAW sql statement.
     *
     * @return array|bool|int
     */
    public function statement(string $query, array $bindings = [])
    {
        if (preg_match_all('/#__([^\s]+)/', $query, $matches)) {
            foreach ($matches[1] as $table) {
                $query = \str_replace('#__' . $table . ' ', $this->grammar->wrapTable($table) . ' ', $query);
            }
        }

        $from = $this->getTableBaseName();
        $query = \str_replace('#from#', $this->grammar->wrapTable($from), $query);

        $prefix = $this->connection->getTablePrefix();
        $query = \str_replace('#__', $prefix, $query);

        $type = \trim(\strtolower(\explode(' ', $query)[0]));

        switch ($type) {
            case 'delete':
                return $this->connection->delete($query, $bindings);

                break;
            case 'update':
                return $this->connection->update($query, $bindings);

                break;
            case 'insert':
                return $this->connection->insert($query, $bindings);

                break;
            case 'select':
                if (\preg_match('/outfile\s/i', $query)) {
                    return $this->connection->statement($query, $bindings);
                }

                return $this->connection->select($query, $bindings);

                break;
            case 'load':
                return $this->connection->unprepared($query);

                break;
        }

        return $this->connection->statement($query, $bindings);
    }

    public function toQuery(): ?string
    {
        $this->atomicEvent('select');

        $sql = $this->toSql();

        foreach ($this->getBindings() as $binding) {
            $value = \is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = \preg_replace('/\?/', (string) $value, $sql, 1);
        }

        return $sql;
    }

    /**
     * get the value from expression.
     *
     * @param  float|\Illuminate\Contracts\Database\Query\Expression|int|string  $value
     */
    protected function getExpressionValue($value): string
    {
        if ($value instanceof QueryExpression) {
            return (string) $value->getValue($this->grammar);
        }

        return (string) $value;
    }
}
