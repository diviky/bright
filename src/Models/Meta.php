<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Meta extends Model
{
    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.meta', 'app_meta');
    }
}
