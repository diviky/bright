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
        try {
            Cache::set('ping', carbon());
        } catch (\Exception $e) {
            return response('failed', 500);
        }

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return response('failed', 500);
        }

        return response('pong');
    }
}
