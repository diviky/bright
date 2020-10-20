<?php

namespace Diviky\Bright\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsUserActivated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param null|mixed               $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!Auth::guard($guard)->check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (0 == $user->status) {
            return redirect()->route('user.activate');
        }

        if (!empty($user->deleted_at)) {
            return abort(401, 'Account Deleted');
        }

        if (1 != $user->status) {
            return abort(401, 'Account Suspended');
        }

        return $next($request);
    }
}
