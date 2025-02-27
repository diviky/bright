<?php

declare(strict_types=1);

namespace Diviky\Bright\Facades;

use Illuminate\Support\Facades\Facade;

class ShardManager extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor()
    {
        return 'bright.shardmanager';
    }
}
