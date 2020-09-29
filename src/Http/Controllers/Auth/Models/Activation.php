<?php

namespace Karla\Http\Controllers\Auth\Models;

use Karla\Database\Eloquent\Model;
use Karla\Models\Models;

class Activation extends Model
{
    public function getTable()
    {
        return config('karla.table.activations', 'activations');
    }

    public function user()
    {
        return $this->belongsTo(Models::user());
    }

}
