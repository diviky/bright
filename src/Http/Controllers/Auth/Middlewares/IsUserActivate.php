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
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->status == 0) {
            return redirect()->route('user.activate');
        }

        if (Auth::check() && Auth::user()->status != 1) {
            return view('auth.disabled');
        }

        return $next($request);
    }
}
