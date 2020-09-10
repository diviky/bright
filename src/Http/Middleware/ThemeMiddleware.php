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
        $this->setUpThemeFromRequest($request);

        return $next($request);
    }
}
