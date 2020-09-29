<?php

namespace Karla\Http\Controllers\Auth\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    public function getTable()
    {
        return config('karla.table.roles', parent::getTable());
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('karla.table.role_permissions'),
            'role_id',
            'permission_id'
        )->where('guard_name', '=', $this->attributes['guard_name'])
            ->withPivot('is_exclude');
    }
}
