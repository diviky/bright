<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Karla\Traits\Themable;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    use Themable;

    public function handle($request, Closure $next, $permission)
    {
        if (app('auth')->guest()) {
            $this->theme($request);
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission)
        ? $permission
        : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (app('auth')->user()->can($permission)) {
                return $next($request);
            }
        }

        $this->theme($request);
        throw UnauthorizedException::forPermissions($permissions);
    }

    protected function theme($request)
    {
        $route = $request->route();
        $action = $route->getActionName();
        $this->setUpThemeFromAction($action);
    }
}
