<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('bright.table.meta', 'app_meta');
    }
}
