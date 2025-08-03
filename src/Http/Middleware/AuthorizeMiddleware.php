<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Exceptions\UnauthorizedException;
use Diviky\Bright\Services\Resolver;
use Illuminate\Routing\Route;

class AuthorizeMiddleware
{
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
            Resolver::theme($request);

            $action = $route->getActionName();

            throw UnauthorizedException::forPermissions([$action]);
        }

        return $next($request);
    }
}
