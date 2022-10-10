<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Diviky\Bright\Concerns\Authorize;
use Diviky\Bright\Concerns\Themable;
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
     * @return Closure
     *
     * @throws UnauthorizedException
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
