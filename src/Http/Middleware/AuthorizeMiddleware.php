<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Concerns\Themable;
use Illuminate\Routing\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AuthorizeMiddleware
{
    use Themable;

    /**
     * Check the user is authorized.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Closure
     *
     * @throws UnauthorizedException
     */
    public function handle($request, \Closure $next)
    {
        $route = $request->route();
        $user = $request->user();

        if (isset($route) && $route instanceof Route && !$user->isAuthorizedAny($route)) {
            $this->setUpThemeFromRequest($request);

            $action = $route->getActionName();

            throw UnauthorizedException::forPermissions([$action]);
        }

        return $next($request);
    }
}
