<?php

namespace Karla;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Karla\Http\Controllers\Auth\Traits\AccessToken;
use Karla\Http\Controllers\Auth\Traits\Authorizable;
use Karla\Http\Controllers\Auth\Traits\HasRoles;
use Karla\Http\Controllers\Auth\Traits\UserParent;
use Karla\Http\Controllers\Auth\Traits\UserRole;
use Karla\Http\Controllers\Auth\Traits\UsersParent;

class User extends Authenticatable
{
    use HasRoles;
    use Authorizable;
    use AccessToken;
    use UserParent;
    use UserRole;
    use UsersParent;

    public $guard_name = 'web';
    protected $admin   = 'super-admin';

    /**
     * The column name of the "Api Token" token.
     *
     * @var string
     */
    protected $apiTokenName = 'access_token';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
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

    public function getTable()
    {
        return config('karla.table.users', 'users');
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }
}
