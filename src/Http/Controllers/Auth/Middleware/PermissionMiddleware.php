<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Karla\Traits\Themable;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    use Themable;

    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = \is_array($permission)
        ? $permission
        : \explode('|', $permission);

        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return $next($request);
            }
        }

        $this->setUpThemeFromRequest($request);

        throw UnauthorizedException::forPermissions($permissions);
    }
}
