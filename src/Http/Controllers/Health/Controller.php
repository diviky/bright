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

    /**
     * Check if the server is ready to receive traffic
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function readiness()
    {
        return response('ok', 200);
    }

    /**
     * Check if the backend service is running without problems
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function livenessBackend()
    {
        return response('ok', 200);
    }

    /**
     * Check if the database service is running without problems
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function livenessDatabase()
    {
        // Check database connection
        try {
            DB::connection()->getPdo();
            return response('ok', 200);
        } catch (\Exception $exception) {
            return response("No database connection", 503);
        }
    }

    /**
     * Check if the database service is running without problems
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function livenessCache()
    {
        // Check database connection
        try {
            Cache::set('ping', carbon());
            return response('ok', 200);
        } catch (\Exception $exception) {
            return response("No database connection", 503);
        }
    }

}
