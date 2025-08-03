<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Exceptions\UnauthorizedException;
use Diviky\Bright\Services\Resolver;
use Illuminate\Support\Facades\Auth;

class RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|string  $roleOrPermission
     * @return \Closure
     *
     * @throws UnauthorizedException
     */
    public function handle($request, \Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            Resolver::theme($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $user = Auth::user();

        $rolesOrPermissions = \is_array($roleOrPermission)
        ? $roleOrPermission
        : \explode('|', $roleOrPermission);

        if ($user && !$user->hasAnyRole($rolesOrPermissions) && !$user->hasAnyPermission($rolesOrPermissions)) {
            Resolver::theme($request);

            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }
}
