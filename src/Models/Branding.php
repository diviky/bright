<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class Branding extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('bright.table.branding', 'branding');
    }
}
