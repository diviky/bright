<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Concerns\AccessToken;
use Diviky\Bright\Concerns\Authorizable;
use Diviky\Bright\Database\Concerns\Connector;
use Diviky\Bright\Database\Eloquent\Concerns\Connection;
use Diviky\Bright\Database\Eloquent\Concerns\Eloquent;
use Diviky\Bright\Http\Controllers\Account\Traits\UserAvatarTrait;
use Diviky\Bright\Http\Controllers\Auth\Concerns\HasRoles;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UserParent;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UserRole;
use Diviky\Bright\Http\Controllers\Auth\Concerns\UsersParent;
use Diviky\Bright\Models\Concerns\Scopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use AccessToken;
    use Authorizable;
    use Connection;
    use Connector;
    use Eloquent;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use Scopes;
    use UserAvatarTrait;
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
     * Check the user has permission.
     *
     * @param  string  $permission
     * @param  null|string  $guardName
     *
     * @SuppressWarnings(PHPMD)
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }

    /**
     * Get the user first role.
     *
     * @return null|string
     */
    public function role()
    {
        return $this->getRoleNames()->first();
    }
}
