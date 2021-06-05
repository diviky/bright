<?php

declare(strict_types=1);

namespace Diviky\Bright;

use Diviky\Bright\Concerns\AccessToken;
use Diviky\Bright\Concerns\Authorizable;
use Diviky\Bright\Http\Controllers\Account\Traits\UserAvatarTrait;
use Diviky\Bright\Http\Controllers\Auth\Traits\HasRoles;
use Diviky\Bright\Http\Controllers\Auth\Traits\UserParent;
use Diviky\Bright\Http\Controllers\Auth\Traits\UserRole;
use Diviky\Bright\Http\Controllers\Auth\Traits\UsersParent;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;
    use Authorizable;
    use AccessToken;
    use UserParent;
    use UserRole;
    use UsersParent;
    use UserAvatarTrait;

    /**
     * Guard name.
     *
     * @var string
     */
    public $guard_name = 'web';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile',
        'password',
        'access_token',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getTable(): string
    {
        return config('bright.table.users', 'users');
    }

    /**
     * Check the user has permission.
     *
     * @param string      $permission
     * @param null|string $guardName
     * @SuppressWarnings(PHPMD)
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }
}
