<?php

namespace Karla\Database\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Karla\Database\Traits\Events;

class Builder extends BaseBuilder
{
    use Events;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * @{inheritdoc}
     */
    public function insert(array $values)
    {
        $values = $this->insertEvent($values);

        return parent::insert($values);
    }

    /**
     * @{inheritdoc}
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $values = $this->insertEvent($values);

        $id = parent::insertGetId($values, $sequence);

        if (empty($id)) {
            $id = $this->getLastId();
        }

        return $id;
    }
    /**
     * @{inheritdoc}
     */
    public function delete($id = null)
    {
        $this->atomicEvent('delete');
        return parent::delete($id);
    }

    /**
     * @{inheritdoc}
     */
    public function exists()
    {
        $this->atomicEvent('select');
        return parent::exists();
    }

    /**
     * @{inheritdoc}
     */
    public function find($id, $columns = ['*'])
    {
        $this->atomicEvent('select');
        return parent::find($id, $columns);
    }

    /**
     * @{inheritdoc}
     */
    public function update(array $values)
    {
        if ($this->usesTimestamps()) {
            $values['updated_at'] = $this->freshTimestamp();
        }

        $values = $this->updateEvent($values);

        return parent::update($values);
    }
    /**
     * @{inheritdoc}
     */
    public function get($columns = ['*'])
    {
        $this->atomicEvent('select');

        return parent::get($columns);
    }

    public function first($columns = ['*'])
    {
        $this->atomicEvent('select');
        return parent::first($columns);
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
        $rows->map(function ($row) use (&$i) {
            $row->serial = ++$i;

            if (isset($row->created_at)) {
                $row->created = date('M d, Y h:i A', strtotime($row->created_at));
            }

            if (isset($row->updated_at)) {
                $row->updated = date('M d, Y h:i A', strtotime($row->updated_at));
            }
        });

        return $rows;
    }
}
