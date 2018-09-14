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
