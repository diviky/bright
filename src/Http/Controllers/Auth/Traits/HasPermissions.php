<?php

namespace Karla\Http\Controllers\Auth\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Traits\HasPermissions as SpatieHasPermissions;

trait HasPermissions
{
    use SpatieHasPermissions;

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        )->withPivot('is_exclude');
    }
}
