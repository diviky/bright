<?php

namespace Karla\Database\Query;

use Illuminate\Database\Query\Builder as LaravelBuilder;
use Karla\Database\Karla;
use Karla\Database\Traits\Build;
use Karla\Database\Traits\Cachable;
use Karla\Database\Traits\Eventable;
use Karla\Database\Traits\Filter;
use Karla\Database\Traits\Ordering;
use Karla\Database\Traits\Outfile;
use Karla\Database\Traits\Raw;
use Karla\Database\Traits\Remove;
use Karla\Database\Traits\Tables;
use Karla\Database\Traits\Timestamps;
use Karla\Helpers\Iterator\SelectIterator;

class Builder extends LaravelBuilder
{
    use Cachable {
        Cachable::get as cacheGet;
    }
    use Outfile;
    use Timestamps;
    use Raw;
    use Build;
    use Eventable;
    use Ordering;
    use Filter;
    use Remove;
    use Tables;

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

    public function softDelete($id = null, $column = 'id', $updated_at = true)
    {
        if ($id) {
            $this->where($column, $id);
        }

        $time = $this->freshTimestamp();

        $values = [
            'deleted_at' => $time,
        ];

        if ($updated_at) {
            $values['updated_at'] = $time;
        }

        return $this->update($values);
    }

    public function noTrash()
    {
        $this->where('deleted_at', null);

        return $this;
    }

    public function onlyTrash()
    {
        $this->where('deleted_at', '<>', null);

        return $this;
    }

    public function paging($perPage = 25, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $perPage = \is_null($perPage) ? 25 : $perPage;

        if (\count($this->tables) > 0) {
            $rows = $this->paginateComplex($perPage, $columns, $pageName, $page);
        } else {
            $rows = $this->paginate($perPage, $columns, $pageName, $page);
        }

        $rows->serial = $rows->firstItem();

        $i = $rows->perPage() * ($rows->currentPage() - 1);
        $rows->transform(function ($row) use (&$i) {
            $row->serial = ++$i;

            return $row;
        });

        return $rows;
    }

    public function statement($sql, $bindings = [], $useReadPdo = false)
    {
        $type = \trim(\strtolower(\explode(' ', $sql)[0]));

        if ($useReadPdo) {
            return $this->connection->statement($sql, $bindings, $useReadPdo);
        }

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
                    return $this->connection->statement($sql, $bindings, true);
                }

                    return $this->connection->select($sql, $bindings);

                break;
            case 'load':
                return $this->connection->affectingStatement($sql, $bindings);

                break;
        }

        return $this->connection->statement($sql, $bindings, $useReadPdo);
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
        $sql = (new Karla())->conditions($where);

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
