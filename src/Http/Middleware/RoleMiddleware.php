<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Diviky\Bright\Traits\Themable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    use Themable;

    /**
     * Check the user for specific roles.
     *
     * @param \Illuminate\Http\Request $request
     * @param array|string             $role
     *
     * @throws UnauthorizedException
     *
     * @return Closure
     */
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::notLoggedIn();
        }

        $user = Auth::user();
        $roles = \is_array($role)
        ? $role
        : \explode('|', $role);

        if ($user && !$user->hasAnyRole($roles)) {
            $this->setUpThemeFromRequest($request);

            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
