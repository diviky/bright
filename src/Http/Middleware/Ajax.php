<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Http\FormRequestFormat;

class Ajax
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if ($request->input('_request') == 'iframe') {
            $request->headers->set('Accept', 'application/json');
        }

        FormRequestFormat::prepare($request);

        return $next($request);
    }
}