<?php

namespace Karla\Http\Controllers\Auth\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string      $name
     * @param string|null $guardName
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        list($option, $view) = explode('.', $name);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $name,
        ];

        $permissions = static::getPermissions();

        $granted = null;

        foreach ($permissions as $permission) {
            if ($permission->guard_name === $guardName) {
                foreach ($matches as $match) {
                    if (Str::is($permission->name, $match)) {
                        $granted = $permission;
                        break 2;
                    }
                }
            }
        }

        if (!$granted) {
            throw PermissionDoesNotExist::create($name, $guardName);
        }

        return $granted;
    }
}
