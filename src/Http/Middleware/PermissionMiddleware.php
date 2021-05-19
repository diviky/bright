<?php

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Diviky\Bright\Traits\Themable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    use Themable;

    /**
     * Check the user for specific permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param array|string             $permission
     *
     * @throws UnauthorizedException
     *
     * @return Closure
     */
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);

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

        $this->setUpThemeFromRequest($request);

        throw UnauthorizedException::forPermissions($permissions);
    }
}
