<?php

namespace Diviky\Bright\Database\Query;

use Diviky\Bright\Database\Bright;
use Diviky\Bright\Database\Traits\Async;
use Diviky\Bright\Database\Traits\Build;
use Diviky\Bright\Database\Traits\Cachable;
use Diviky\Bright\Database\Traits\Eventable;
use Diviky\Bright\Database\Traits\Filter;
use Diviky\Bright\Database\Traits\Ordering;
use Diviky\Bright\Database\Traits\Outfile;
use Diviky\Bright\Database\Traits\Paging;
use Diviky\Bright\Database\Traits\Raw;
use Diviky\Bright\Database\Traits\Remove;
use Diviky\Bright\Database\Traits\SoftDeletes;
use Diviky\Bright\Database\Traits\Tables;
use Diviky\Bright\Database\Traits\Timestamps;
use Diviky\Bright\Helpers\Iterator\SelectIterator;
use Illuminate\Database\Query\Builder as LaravelBuilder;

class Builder extends LaravelBuilder
{
    use Async;
    use Build;
    use Cachable {
        Cachable::get as cacheGet;
    }
    use Eventable;
    use Filter;
    use Ordering;
    use Outfile;
    use Paging;
    use Raw;
    use Remove;
    use SoftDeletes;
    use Tables;
    use Timestamps;

    /**
     * {@inheritdoc}
     */
    public function pluck($column, $key = null)
    {
        $this->atomicEvent('select');

        return parent::pluck($column, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        $this->atomicEvent('select');

        return $this->cacheGet($columns);
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
     * {@inheritdoc}
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

    public function update(array $values)
    {
        $values = $this->updateEvent($values);

        return parent::update($values);
    }

    public function statement($sql, array $bindings = [])
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

    public function flatChunk($count = 1000, $callback = null)
    {
        $results = $this->forPage($page = 1, $count)->get();

        while (\count($results) > 0) {
            if ($callback) {
                foreach ($results as $result) {
                    yield $result = $callback($result);
                }
            } else {
                // Flatten the chunks out
                foreach ($results as $result) {
                    yield $result;
                }
            }

            ++$page;

            $results = $this->forPage($page, $count)->get();
        }
    }

    public function iterate($count = 10000, $callback = null)
    {
        return $this->iterator($count, $callback);
    }

    public function iterator($count = 10000, $callback = null)
    {
        return new SelectIterator($this, $count, $callback);
    }

    public function whereWith($where = [], $bindings = [])
    {
        $sql = (new Bright())->conditions($where);

        return $this->whereRaw($sql, $bindings);
    }

    public function toQuery()
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
