<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class SocialiteUser extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return 'auth_socialite_users';
    }
}
