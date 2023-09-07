<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class PasswordHistory extends Model
{
    public $timestamps = false;

    public function getTable()
    {
        return config('bright.table.password_history', 'password_history');
    }
}
