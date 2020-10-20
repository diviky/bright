<?php

namespace Diviky\Bright\Http\Controllers\Auth\Middleware;

use Closure;
use Diviky\Bright\Traits\Authorize;
use Diviky\Bright\Traits\Themable;
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
