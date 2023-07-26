<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class UserUsers extends Model
{
    public $timestamps = false;

    public function getTable()
    {
        return config('bright.table.user_users', 'user_users');
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Models::user(), 'parent_id');
    }
}
