<?php

namespace Karla\Http\Controllers\Auth\Traits;

trait UserRole
{
    public function assignOwnRole($role)
    {
        $this->role = $role;

        return $this->save();
    }

    public function removeOwnRole()
    {
        $this->role = null;

        return $this->save();
    }
}
