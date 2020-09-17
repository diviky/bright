<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $table = 'auth_password_history';
    public $guarded  = [];
}
