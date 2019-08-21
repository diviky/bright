<?php

namespace Karla\Database\Traits;

trait SoftDeletes
{
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
        return $this->withOutTrashed();
    }

    public function onlyTrash()
    {
        return $this->onlyTrashed();
    }

    public function onlyTrashed()
    {
        $this->where('deleted_at', '<>', null);

        return $this;
    }

    public function withTrashed()
    {
        return $this;
    }

    public function withOutTrashed()
    {
        $this->whereNull('deleted_at');

        return $this;
    }
}
