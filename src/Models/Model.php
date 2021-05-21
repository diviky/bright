<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Model extends BaseModel
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    public $guarded  = [];

    /**
     * Scope active column.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope active column.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|int                           $user_id
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMe($query, $user_id = null)
    {
        $user_id = $user_id ?? user('id');

        return $query->where('user_id', $user_id);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Models::user());
    }
}
