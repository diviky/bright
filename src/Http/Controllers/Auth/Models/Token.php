<?php

namespace Karla\Http\Controllers\Auth\Models;

use Karla\Database\Eloquent\Model;
use Karla\User;

class Token extends Model
{
    protected $table = 'auth_tokens';

    protected $fillable = [
        'access_token',
        'user_id',
        'refresh_token',
        'whitelist_ips',
        'blocklist_ips',
        'expires_in',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
