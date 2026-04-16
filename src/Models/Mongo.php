<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\MongoDB\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Mongo extends BaseModel
{
    use HasUuids;

    protected $connection = 'mongodb';

    protected $guarded = [];

    protected static $userModel = 'App\Models\User';
}
