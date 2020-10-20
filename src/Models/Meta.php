<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class Meta extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('bright.table.meta', 'app_meta');
    }
}
