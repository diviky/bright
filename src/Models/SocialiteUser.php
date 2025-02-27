<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class SocialiteUser extends Model
{
    protected $casts = [
        'payload' => 'array',
    ];

    #[\Override]
    public function getTable()
    {
        return 'auth_socialite_users';
    }
}
