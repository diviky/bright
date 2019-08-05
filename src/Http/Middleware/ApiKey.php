<?php

namespace Karla\Http\Middleware;

use Closure;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->merge(['access_token' => $request->input('api_key')]);

        if (!empty($request->input('format'))) {
            $request->merge(['format' => 'json']);
        }

        return $next($request);
    }
}
