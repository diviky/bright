<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Karla\Traits\Themable;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    use Themable;

    public function handle($request, Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesOrPermissions = is_array($roleOrPermission)
        ? $roleOrPermission
        : explode('|', $roleOrPermission);

        if (!Auth::user()->hasAnyRole($rolesOrPermissions) && !Auth::user()->hasAnyPermission($rolesOrPermissions)) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }
}
