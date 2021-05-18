<?php

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Model;

class UserUsers extends Model
{
    public $guarded       = [];

    protected $timestamps = false;

    public function getTable()
    {
        return config('bright.table.user_users', 'user_users');
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
