<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;
use Karla\Models\Models;

class UserUsers extends Model
{
    protected $timestamps = false;

    public $guarded = [];

    public function getTable()
    {
        return config('karla.table.user_users', 'user_users');
    }

    public function parent()
    {
        return $this->belongTo(Models::user(), 'parent_id');
    }

    public function user()
    {
        return $this->belongTo(Models::user(), 'user_id');
    }
}
