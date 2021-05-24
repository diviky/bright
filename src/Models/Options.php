<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Options extends Model
{
    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return 'app_options';
    }
}
