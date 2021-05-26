<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

use Diviky\Bright\Database\Meta as BaseMeta;

class Meta extends BaseMeta
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'app_meta';

    /**
     * Relation table name.
     *
     * @var string
     */
    protected $relation = 'app_meta_values';
}
