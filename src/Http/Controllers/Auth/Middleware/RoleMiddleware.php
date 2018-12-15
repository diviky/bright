<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Karla\Traits\Themable;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    use Themable;

    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            $this->theme($request);
            throw UnauthorizedException::notLoggedIn();
        }

        $roles = is_array($role)
        ? $role
        : explode('|', $role);

        if (!Auth::user()->hasAnyRole($roles)) {
            $this->theme($request);
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }

    protected function theme($request)
    {
        $route  = $request->route();
        $action = $route->getActionName();
        $this->setUpThemeFromAction($action);
    }
}
