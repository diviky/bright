<?php

namespace Karla\Http\Controllers\Auth\Traits;

use App\User;

trait UserParent
{
    public function assignParent($parent_id = null)
    {
        if (null === $parent_id) {
            if (app()->has('owner')) {
                $parent_id = app()->get('owner');
            } else {
                $parent_id = User::where('role', $this->admin)
                    ->orderBy('id', 'asc')
                    ->take(1)
                    ->value('id');
            }
        }

        $this->parent_id = $parent_id;

        return $this->save();
    }

    public function removeParent()
    {
        $this->parent_id = null;

        return $this->save();
    }

    public function parent()
    {
        return $this->hasOne('App\Users', 'parent_id');
    }
}
