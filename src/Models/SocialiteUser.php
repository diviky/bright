<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialiteUser extends Model
{
    use HasFactory;

    public $guarded      = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function getTable()
    {
        return 'auth_socialite_users';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
