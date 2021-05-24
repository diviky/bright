<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query;

use Diviky\Bright\Database\Bright;
use Diviky\Bright\Database\Traits\Async;
use Diviky\Bright\Database\Traits\Build;
use Diviky\Bright\Database\Traits\BuildsQueries;
use Diviky\Bright\Database\Traits\Cachable;
use Diviky\Bright\Database\Traits\Eventable;
use Diviky\Bright\Database\Traits\Filter;
use Diviky\Bright\Database\Traits\Ordering;
use Diviky\Bright\Database\Traits\Outfile;
use Diviky\Bright\Database\Traits\Paging;
use Diviky\Bright\Database\Traits\Raw;
use Diviky\Bright\Database\Traits\Remove;
use Diviky\Bright\Database\Traits\SoftDeletes;
use Diviky\Bright\Database\Traits\Timestamps;
use Illuminate\Database\Query\Builder as LaravelBuilder;

class Builder extends LaravelBuilder
{
    use Async;
    use Build;
    use Cachable {
        Cachable::get as cachableGet;
        Cachable::pluck as cachablePluck;
    }
    use Eventable;
    use Filter;
    use Ordering;
    use Outfile;
    use Paging;
    use Raw;
    use Remove;
    use SoftDeletes;
    use Timestamps;
    use BuildsQueries;

    /**
     * Set the alias for table.
     *
     * @param string $as
     */
    public function alias($as): self
    {
        $this->from = "{$this->from} as {$as}";

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function pluck($column, $key = null)
    {
        return $this->cachablePluck($column, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        return $this->cachableGet($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        $this->atomicEvent('select');

        return parent::exists();
    }

    /**
     * {@inheritdoc}
     */
    public function insert(array $values)
    {
        $values = $this->insertEvent($values);

        return parent::insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param null|string $sequence
     *
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

    /**
     * {@inheritdoc}
     */
    public function delete($id = null)
    {
        $this->atomicEvent('delete');

        return parent::delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function update(array $values)
    {
        $values = $this->updateEvent($values);

        return parent::update($values);
    }

    /**
     * Excecute the RAW sql statement.
     *
     * @return array|bool|int
     */
    public function statement(string $sql, array $bindings = [])
    {
        $prefix = $this->connection->getTablePrefix();
        $sql    = \str_replace('#__', $prefix, $sql);

        $type = \trim(\strtolower(\explode(' ', $sql)[0]));

        switch ($type) {
            case 'delete':
                return $this->connection->delete($sql, $bindings);

                break;
            case 'update':
                return $this->connection->update($sql, $bindings);

                break;
            case 'insert':
                return $this->connection->insert($sql, $bindings);

                break;
            case 'select':
                if (\preg_match('/outfile\s/i', $sql)) {
                    return $this->connection->statement($sql, $bindings);
                }

                return $this->connection->select($sql, $bindings);

                break;
            case 'load':
                return $this->connection->unprepared($sql);

                break;
        }

        return $this->connection->statement($sql, $bindings);
    }

    /**
     * Old cakephp style conditions.
     *
     * @param array $where
     * @param array $bindings
     */
    public function whereWith($where = [], $bindings = []): self
    {
        $sql = (new Bright())->conditions($where);

        return $this->whereRaw($sql, $bindings);
    }

    public function toQuery(): ?string
    {
        $this->atomicEvent('select');

        $sql = $this->toSql();

        foreach ($this->getBindings() as $binding) {
            $value = \is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql   = \preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }
}
