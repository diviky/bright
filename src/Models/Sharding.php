<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Sharding extends Model
{
    #[\Override]
    public function getTable()
    {
        return 'sharding';
    }
}
