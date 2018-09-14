<?php

namespace Karla;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Karla\Http\Controllers\Auth\Traits\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
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
        'name', 'username', 'email', 'mobile', 'password', 'api_token', 'status',
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
