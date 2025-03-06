<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model as BaseModel;
use Diviky\Bright\Models\Concerns\Scopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Model extends BaseModel
{
    use HasFactory;
    use Scopes;

    public $guarded = [];

    protected static $userModel = 'App\Models\User';
}
