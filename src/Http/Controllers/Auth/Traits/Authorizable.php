<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Illuminate\Support\Str;

trait Authorizable
{
    /**
     * Check user as right permission.
     *
     * @param string $ability
     *
     * @return null|mixed
     */
    protected function isMatched($ability)
    {
        list($option, $view) = \array_pad(\explode('.', $ability), 2, null);

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

        $revoke = false;
        foreach ($permissions as $permission) {
            foreach ($matches as $match) {
                if (Str::is($permission->name, $match)) {
                    $pivot = $permission->pivot;
                    if ($pivot && $pivot->is_exclude) {
                        $revoke = true;

                        break 2;
                    }
                }
            }
        }

        if ($revoke) {
            return $granted;
        }

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
