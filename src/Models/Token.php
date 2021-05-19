<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class Token extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'allowed_ip',
        'expires_in',
    ];

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.tokens', 'tokens');
    }
}
