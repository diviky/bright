<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    #[\Override]
    public function getTable()
    {
        return config('bright.table.roles', parent::getTable());
    }

    #[\Override]
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            config('bright.table.role_permissions'),
            'role_id',
            'permission_id'
        )->where('guard_name', '=', $this->attributes['guard_name'])
            ->withPivot('is_exclude');
    }
}
