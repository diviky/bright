<?php

namespace Diviky\Bright\Models;

class Branding extends Model
{
    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.branding', 'branding');
    }
}
