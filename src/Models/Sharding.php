<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Model;

class Sharding extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('bright.table.sharding', 'sharding');
    }
}
