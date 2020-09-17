<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class Options extends Model
{
    protected $table = 'app_options';
    public $guarded  = [];
}
