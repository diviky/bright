<?php

namespace Diviky\Bright\Models;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission as BasePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends BasePermission
{
    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(array $params = []): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions($params);
    }
}
