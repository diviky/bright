<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class Meta extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('karla.table.meta', 'app_meta');
    }
}
