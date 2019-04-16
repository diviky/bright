<?php

namespace Karla\Http\Controllers\Auth\Models;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission as BasePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends BasePermission
{
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('permission.guard');

        parent::__construct($attributes);
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(array $params = []): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions($params);
    }
}
