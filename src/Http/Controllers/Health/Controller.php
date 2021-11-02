<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Health;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    public function getViewsFrom(): array
    {
        return [__DIR__];
    }

    public function ping()
    {
        Cache::set('ping', carbon());
        DB::connection()->getPdo();

        return response('pong');
    }
}
