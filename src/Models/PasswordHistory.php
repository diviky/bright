<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('karla.table.password_history', 'password_history');
    }
}
