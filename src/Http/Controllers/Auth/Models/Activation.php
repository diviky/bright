<?php

namespace Karla\Http\Controllers\Auth\Models;

use Karla\Database\Eloquent\Model;
use Karla\User;

class Activation extends Model
{
    protected $table = 'auth_activations';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
