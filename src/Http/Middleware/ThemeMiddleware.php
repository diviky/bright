<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Concerns\Themable;

class ThemeMiddleware
{
    use Themable;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $this->setUpThemeFromRequest($request);

        return $next($request);
    }
}
