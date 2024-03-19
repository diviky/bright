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
        $request->merge(['access_token' => $request->input('api_key')]);
        $request->headers->set('Accept', 'application/json');

        if (!empty($request->input('format'))) {
            $request->merge(['format' => 'json']);
        }

        return $next($request);
    }
}
