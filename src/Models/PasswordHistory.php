<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $guarded = [];

    public function getTable()
    {
        return config('bright.table.password_history', 'password_history');
    }
}
