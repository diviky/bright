<?php

namespace Karla\Http\Controllers\Auth\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('permission.guard_name');

        parent::__construct($attributes);
    }

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id'
        )->where('guard_name', '=', $this->attributes['guard_name'])
            ->withPivot('is_exclude');
    }
}
