<?php

declare(strict_types=1);

namespace Diviky\Bright\Models\Concerns;

use Diviky\Bright\Models\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Scopes
{
    /**
     * Scope active column.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope active column.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  null|int  $user_id
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMe($query, $user_id = null)
    {
        $user_id = $user_id ?? user('id');

        return $query->where('user_id', $user_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Models::user());
    }
}
