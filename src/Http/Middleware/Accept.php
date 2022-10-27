<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;

class Accept
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $request->merge(['format' => 'json']);

        return $next($request);
    }
}
