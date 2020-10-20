<?php

namespace Diviky\Bright\Http\Controllers\Auth\Models;

use Diviky\Bright\Database\Eloquent\Model;
use Diviky\Bright\Models\Models;
use Diviky\Bright\User;

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
        return config('bright.table.tokens', 'tokens');
    }

    public function user()
    {
        return $this->belongsTo(Models::user(), 'user_id', 'id');
    }
}
