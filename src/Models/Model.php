<?php

namespace Diviky\Bright\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use HasFactory;

    public $guarded  = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeMe($query, $user_id = null)
    {
        $user_id = $user_id ?? user('id');

        return $query->where('user_id', $user_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
