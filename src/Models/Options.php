<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class Options extends Model
{
    public $guarded  = [];
    protected $table = 'app_options';
}
