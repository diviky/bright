<?php

namespace Karla\Http\Controllers\Auth\Models;

use Karla\User;
use Illuminate\Database\Eloquent\Model;

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
