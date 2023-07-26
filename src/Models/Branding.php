<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Branding extends Model
{
    public function getTable()
    {
        return config('bright.table.branding', 'branding');
    }
}
