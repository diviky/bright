<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Concerns\Themable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    use Themable;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param array|string             $roleOrPermission
     *
     * @return \Closure
     *
     * @throws UnauthorizedException
     */
    public function handle($request, \Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $user = Auth::user();

        $rolesOrPermissions = \is_array($roleOrPermission)
        ? $roleOrPermission
        : \explode('|', $roleOrPermission);

        if ($user && !$user->hasAnyRole($rolesOrPermissions) && !$user->hasAnyPermission($rolesOrPermissions)) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }
}
