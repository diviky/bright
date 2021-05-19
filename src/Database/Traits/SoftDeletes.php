<?php

namespace Diviky\Bright\Database\Traits;

trait SoftDeletes
{
    public function softDelete($id = null, $column = 'id', $updated_at = true): int
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

    public function noTrash(): self
    {
        return $this->withOutTrashed();
    }

    public function onlyTrash(): self
    {
        return $this->onlyTrashed();
    }

    public function onlyTrashed(): self
    {
        $this->where('deleted_at', '<>', null);

        return $this;
    }

    public function withTrashed(): self
    {
        return $this;
    }

    public function withOutTrashed(): self
    {
        $this->whereNull('deleted_at');

        return $this;
    }
}
