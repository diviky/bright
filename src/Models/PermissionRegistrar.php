<?php

namespace Diviky\Bright\Http\Controllers\Auth\Models;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar as BasePermissionRegistrar;

class PermissionRegistrar extends BasePermissionRegistrar
{
    public function getPermissions(): Collection
    {
        return $this->cache->remember($this->cacheKey, config('permission.cache_expiration_time'), function () {
            return app(Permission::class)->with('roles')->get();
        });
    }
}
