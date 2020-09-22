<?php

namespace Karla\Util;

use Karla\Database\Meta as BaseMeta;

class Meta extends BaseMeta
{
    protected $table    = 'app_meta';
    protected $relation = 'app_meta_values';
}
