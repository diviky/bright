<?php

namespace Karla\Http\Middleware;

use Closure;
use Karla\Traits\Themable;

class ThemeMiddleware
{
    use Themable;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route  = $request->route();
        $action = $route->getActionName();
        $this->setUpThemeFromAction($action);

        return $next($request);
    }
}
