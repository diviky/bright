<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;
use Karla\Models\Models;

class UserUsers extends Model
{
    protected $table      = 'auth_user_users';
    protected $timestamps = false;

    public $guarded = [];

    public function parent()
    {
        return $this->belongTo(Models::user(), 'parent_id');
    }

    public function user()
    {
        return $this->belongTo(Models::user(), 'user_id');
    }
}
