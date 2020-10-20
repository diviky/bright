<?php

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Diviky\Bright\Traits\Themable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    use Themable;

    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $roles = \is_array($role)
        ? $role
        : \explode('|', $role);

        if (!Auth::user()->hasAnyRole($roles)) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
