<?php

namespace Karla\Http\Controllers\Auth\Traits;

use Illuminate\Support\Facades\DB;

trait UsersParent
{
    public function assignParent($parent_id)
    {
        $values = [
            'parent_id' => $parent_id,
            'user_id'   => $this->id,
        ];

        return DB::table('auth_user_users')
            ->timestamps(false)
            ->insert($values);
    }

    public function removeParent()
    {
        return DB::table('auth_user_users')
            ->where('user_id', $this->id)
            ->delete();
    }

    public function getParent()
    {
        return DB::table('auth_users as u')
            ->join('auth_user_users as p', 'p.parent_id', 'u.id')
            ->where('p.user_id', $this->id)
            ->first(['u.*']);
    }
}
