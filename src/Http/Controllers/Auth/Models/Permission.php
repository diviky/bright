<?php

namespace Karla\Http\Controllers\Auth\Models;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
