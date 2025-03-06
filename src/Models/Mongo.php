<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Concerns\Uuids;
use Diviky\Bright\Database\MongoDB\Eloquent\Model as BaseModel;

class Mongo extends BaseModel
{
    use Uuids;

    protected $connection = 'mongodb';

    protected $guarded = [];

    protected static $userModel = 'App\Models\User';
}
