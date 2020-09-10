<?php

namespace Karla\Http\Controllers\Auth\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Karla\Traits\Authorize;
use Karla\Traits\Themable;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AuthorizeMiddleware
{
    use Themable;
    use Authorize;

    public function handle($request, Closure $next)
    {
        $route = $request->route();

        if (!$this->isAuthorizedAny($route)) {
            $action = $route->getActionName();
            $this->setUpThemeFromRequest($request);
            throw UnauthorizedException::forPermissions([$action]);
        }

        return $next($request);
    }
}
