<?php

namespace Karla\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLogs extends Model
{
    public $guarded      = [];
    public $incrementing = false;
    public $timestamps   = false;

    protected $keyType = 'string';

    public function getTable()
    {
        return config('karla.table.email_logs', 'email_logs');
    }
}
