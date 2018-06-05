<?php

namespace Karla\Http\Controllers\Auth\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsUserActivate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Auth::guard($guard)->check()) {
            return $next($request);
        }

        if (Auth::user()->status == 0) {
            return redirect()->route('user.activate');
        }

        if (Auth::user()->status != 1) {
            return view('auth.disabled');
        }

        return $next($request);
    }
}
