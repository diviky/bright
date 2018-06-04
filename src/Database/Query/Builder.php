<?php

namespace Karla\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Karla\Database\Traits\Cachable;
use Karla\Database\Traits\Eventable;

class Builder extends BaseBuilder
{
    use Cachable;
    use Eventable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    protected $timestamps = true;

    /**
     * @{inheritdoc}
     */
    protected function setTimeStamps(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();
            $values['updated_at'] = $time;
            $values['created_at'] = $time;
        }

        return $values;
    }

    protected function setTimeStamp(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();
            $values['updated_at'] = $time;
        }

        return $values;
    }

    /**
     * @{inheritdoc}
     */
    public function get($columns = ['*'])
    {
        $this->atomicEvent('select');

        return parent::get($columns);
    }

    public function groupByRaw($sql, array $bindings = [])
    {
        $this->groupBy(DB::raw($sql));

        if ($bindings) {
            $this->setBindings($bindings, 'group');
            $this->addBinding($bindings, 'group');
        }

        return $this;
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

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function freshTimestamp()
    {
        return new Carbon;
    }

    public function timestamps($allow = true)
    {
        $this->timestamps = $allow;

        return $this;
    }

    /**
     * Determine if the builder uses timestamps.
     *
     * @return bool
     */
    protected function usesTimestamps()
    {
        return $this->timestamps;
    }

    public function paging($perPage = 25, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $rows = $this->paginate($perPage, $columns, $pageName, $page);

        $i = $rows->perPage() * ($rows->currentPage() - 1);
        $rows->transform(function ($row) use (&$i) {
            $row->serial = ++$i;

            if (isset($row->created_at)) {
                $row->created = date('M d, Y h:i A', strtotime($row->created_at));
            }

            if (isset($row->updated_at)) {
                $row->updated = date('M d, Y h:i A', strtotime($row->updated_at));
            }

            return $row;
        });

        return $rows;
    }
}
