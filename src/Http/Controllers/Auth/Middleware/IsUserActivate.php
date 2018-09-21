<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsUserActivate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Auth::guard($guard)->check()) {
            return $next($request);
        }

        if (0 == Auth::user()->status) {
            return redirect()->route('user.activate');
        }

        if (1 != Auth::user()->status) {
            return abort(401, 'Account Suspended');
        }

        return $next($request);
    }
}
