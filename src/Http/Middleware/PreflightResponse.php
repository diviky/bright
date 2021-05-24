<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;

class PreflightResponse
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Closure|\Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        if ('OPTIONS' === $request->getMethod()) {
            $response = response('');

            return $this->addCorsHeaders($response);
        }

        return $next($request);
    }

    /**
     * Add cors headers.
     *
     * @return \Illuminate\Http\Response
     */
    public function addCorsHeaders(\Illuminate\Http\Response $response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization,Origin,Accept,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,*');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
