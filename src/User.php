<?php

namespace Karla;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable as BaseAuthorizable;
use Illuminate\Notifications\Notifiable;
use Karla\Database\Eloquent\Model;
use Karla\Http\Controllers\Auth\Traits\Authorizable;
use Karla\Http\Controllers\Auth\Traits\HasRoles;

class User extends Model implements
AuthenticatableContract,
AuthorizableContract,
CanResetPasswordContract
{
    use Authenticatable;
    use BaseAuthorizable;
    use CanResetPassword;
    use Notifiable;
    use HasRoles;
    use Authorizable;

    public $guard_name = 'web';
    protected $admin   = 'super-admin';
    protected $table   = 'auth_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'mobile', 'password', 'access_token', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }
}
