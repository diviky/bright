<?php

namespace Karla\Middlewares;

use Closure;

class Ajax
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input('_request') == 'iframe') {
            $request->headers->add(['Accept' => 'application/json']);
        }

        return $next($request);
    }
}
