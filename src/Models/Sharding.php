<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Sharding extends Model
{
    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return 'sharding';
    }
}
