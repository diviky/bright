<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model
{
    public function getTable()
    {
        return config('bright.table.activations', 'activations');
    }

    public function user()
    {
        return $this->belongsTo(Models::user());
    }
}
