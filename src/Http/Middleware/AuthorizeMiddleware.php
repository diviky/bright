<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Diviky\Bright\Traits\Authorize;
use Diviky\Bright\Traits\Themable;
use Illuminate\Routing\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AuthorizeMiddleware
{
    use Themable;
    use Authorize;

    /**
     * Check the user is authorized.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws UnauthorizedException
     *
     * @return Closure
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();

        if (isset($route) && $route instanceof Route && !$this->isAuthorizedAny($route)) {
            $this->setUpThemeFromRequest($request);

            $action = $route->getActionName();

            throw UnauthorizedException::forPermissions([$action]);
        }

        return $next($request);
    }
}
