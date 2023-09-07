<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Meta extends Model
{
    public function getTable()
    {
        return config('bright.table.meta', 'app_meta');
    }
}
