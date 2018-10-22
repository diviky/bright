<?php

namespace Karla\Http\Controllers\Auth\Traits;

use Illuminate\Support\Str;

trait Authorizable
{
    protected function isMatched($ability)
    {
        list($option, $view) = explode('.', $ability);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $ability,
        ];

        $permissions = $this->getDirectPermissions();

        $granted = null;
        $revoke  = false;
        foreach ($permissions as $permission) {
            foreach ($matches as $match) {
                if (Str::is($permission->name, $match)) {
                    $pivot = $permission->pivot;
                    if ($pivot && $pivot->is_exclude) {
                        $revoke = true;
                        break 2;
                    }

                    $granted = $permission;
                    break 2;
                }
            }
        }

        if ($revoke || $granted) {
            return $granted;
        }

        $permissions = $this->getAllPermissions();

        $granted = null;
        foreach ($permissions as $permission) {
            foreach ($matches as $match) {
                if (Str::is($permission->name, $match)) {
                    $granted = $permission;
                    break 2;
                }
            }
        }

        return $granted;
    }
}
