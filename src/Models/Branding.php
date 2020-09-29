<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class Branding extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('karla.table.branding', 'branding');
    }

}
