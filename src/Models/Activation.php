<?php

namespace Diviky\Bright\Models;

class Activation extends Model
{
    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.activations', 'activations');
    }
}
