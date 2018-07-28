<?php

namespace Karla\Database\Query;

use Karla\Database\Karla;
use Illuminate\Support\Carbon;
use Karla\Database\Traits\Raw;
use Illuminate\Support\Facades\DB;
use Karla\Database\Traits\Outfile;
use Karla\Database\Traits\Cachable;
use Karla\Database\Traits\Eventable;
use Karla\Database\Traits\Timestamps;
use Karla\Helpers\Iterator\SelectIterator;
use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use Cachable;
    use Eventable;
    use Outfile;
    use Timestamps;
    use Raw;

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

        return parent::get($columns);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($value && is_array($value)) {
            return parent::whereIn($column, $value, $boolean);
        }

        return parent::where($column, $operator, $value, $boolean);
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
        $perPage = is_null($perPage) ? 25 : $perPage;
        $rows    = $this->paginate($perPage, $columns, $pageName, $page);

        $i = $rows->perPage() * ($rows->currentPage() - 1);

        $rows->transform(function ($row) use (&$i) {
            $row->serial = ++$i;

            if (isset($row->created_at)) {
                $row->created = carbon($row->created_at)->format('M d, Y h:i A');
            }

            if (isset($row->updated_at)) {
                $row->updated = carbon($row->updated_at)->format('M d, Y h:i A');
            }

            if (isset($row->deleted_at)) {
                $row->deleted = carbon($row->deleted_at)->format('M d, Y h:i A');
            }

            return $row;
        });

        return $rows;
    }

    public function statement($sql, $bindings = [])
    {
        return $this->connection->statement($sql, $bindings);
    }

    public function flatChunk($count = 1000, $callback = null)
    {
        $results = $this->forPage($page = 1, $count)->get();

        while (count($results) > 0) {
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

    public function iterate($count, $callback = null)
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
        $sql = $this->toSql();
        foreach ($this->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql   = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }
}
