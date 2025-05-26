<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Concerns\AccessToken;
use Diviky\Bright\Database\Concerns\Connector;
use Diviky\Bright\Database\Eloquent\Concerns\Connection;
use Diviky\Bright\Database\Eloquent\Concerns\Eloquent;
use Diviky\Bright\Http\Controllers\Account\Concerns\UserAvatar;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UserParent;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UserRole;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UsersParent;
use Diviky\Bright\Models\Concerns\Scopes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use AccessToken;
    use Connection;
    use Connector;
    use Eloquent;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Scopes;
    use UserAvatar;
    use UserParent;
    use UserRole;
    use UsersParent;

    /**
     * Admin role.
     *
     * @var string
     */
    protected $admin = 'super-admin';

    /**
     * The column name of the "Api Token" token.
     *
     * @var string
     */
    protected $apiTokenName = 'access_token';

    /**
     * Access token column name.
     *
     * @var string
     */
    protected $accessTokenName = 'access_token';

    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile',
        'password',
        'access_token',
        'status',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'options' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    #[\Override]
    public function getTable(): string
    {
        return config('bright.table.users', 'users');
    }

    /**
     * Get the user's first name.
     */
    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRole(),
        );
    }
}
