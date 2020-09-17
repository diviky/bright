<?php

namespace Karla\Models;

use Karla\Database\Eloquent\Model;

class EmailLogs extends Model
{
    protected $table = 'addon_email_logs';
    public $guarded  = [];
}
