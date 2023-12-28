<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $request->mergeIfMissing(['access_token' => $request->input('api_key')]);

        return $next($request);
    }
}
