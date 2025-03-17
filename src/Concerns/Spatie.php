<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Http\Controllers\Auth\Concerns\HasRoles;
use Illuminate\Support\Str;

trait Spatie
{
    use Authorize;
    use HasRoles;

    /**
     * Check user as right permission.
     *
     * @param  string  $ability
     * @return null|mixed
     */
    public function isForbid($ability)
    {
        [$option, $view] = \array_pad(\explode('.', $ability), 2, null);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $ability,
        ];

        $permissions = $this->getDirectPermissions();

        foreach ($permissions as $permission) {
            foreach ($matches as $match) {
                if (Str::is($permission->name, $match)) {
                    $pivot = $permission->pivot;
                    if ($pivot && $pivot->is_exclude) {
                        return true;
                    }
                }
            }
        }

        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permission) {
            foreach ($matches as $match) {
                if (Str::is($permission->name, $match)) {
                    $pivot = $permission->pivot;
                    if ($pivot && $pivot->is_exclude) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check user as right permission.
     *
     * @param  string  $ability
     * @return null|mixed
     */
    protected function isMatched($ability)
    {
        [$option, $view] = \array_pad(\explode('.', $ability), 2, null);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $ability,
        ];

        $permissions = $this->getDirectPermissions();

        $granted = null;
        $revoke = false;
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

    /**
     * Check the user has permission.
     *
     * @param  string  $permission
     * @param  null|string  $guardName
     *
     * @SuppressWarnings(PHPMD)
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }

    /**
     * Get the user first role.
     *
     * @return null|string
     */
    public function getRole()
    {
        return $this->getRoleNames()->first()?->name;
    }

    public function getRoles()
    {
        return $this->getRoleNames();
    }
}
