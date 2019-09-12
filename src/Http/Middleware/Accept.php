<?php

namespace Karla\Http\Middleware;

use Closure;

class Accept
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
        $request->headers->set('Accept', 'application/json');
        $request->merge(['format' => 'json']);

        app()->is_api_request = true;

        return $next($request);
    }
}
