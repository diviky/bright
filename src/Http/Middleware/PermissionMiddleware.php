<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Exceptions\UnauthorizedException;
use Diviky\Bright\Services\Resolver;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Check the user for specific permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array|string  $permission
     * @return \Closure
     *
     * @throws UnauthorizedException
     */
    public function handle($request, \Closure $next, $permission)
    {
        if (Auth::guest()) {
            Resolver::theme($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $user = Auth::user();

        $permissions = \is_array($permission)
        ? $permission
        : \explode('|', $permission);

        if (isset($user)) {
            foreach ($permissions as $permission) {
                if ($user->can($permission)) {
                    return $next($request);
                }
            }
        }

        Resolver::theme($request);

        throw UnauthorizedException::forPermissions($permissions);
    }
}
