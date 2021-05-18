<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $guarded      = [];

    public $timestamps   = false;

    public function getTable()
    {
        return config('bright.table.password_history', 'password_history');
    }
}
