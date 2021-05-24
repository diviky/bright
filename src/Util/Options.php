<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

use Diviky\Bright\Database\Options as DbOptions;

class Options extends DbOptions
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'app_options';
}
