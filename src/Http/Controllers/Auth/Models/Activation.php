<?php

namespace Diviky\Bright\Http\Controllers\Auth\Models;

use Diviky\Bright\Database\Eloquent\Model;
use Diviky\Bright\Models\Models;

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
