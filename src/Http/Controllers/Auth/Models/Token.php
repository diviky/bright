<?php

namespace Karla\Http\Controllers\Auth\Models;

use Karla\Database\Eloquent\Model;
use Karla\Models\Models;
use Karla\User;

class Token extends Model
{
    protected $fillable = [
        'access_token',
        'user_id',
        'refresh_token',
        'allowed_ip',
        'expires_in',
    ];

    public function getTable()
    {
        return config('karla.table.tokens', 'tokens');
    }

    public function user()
    {
        return $this->belongsTo(Models::user(), 'user_id', 'id');
    }

}
