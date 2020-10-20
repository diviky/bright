<?php

namespace Diviky\Bright\Util;

use Diviky\Bright\Database\Meta as BaseMeta;

class Meta extends BaseMeta
{
    protected $table    = 'app_meta';
    protected $relation = 'app_meta_values';
}
