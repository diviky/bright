<?php

namespace Karla\Http\Middleware;

use Closure;

class PreflightResponse
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     **/
    public function handle($request, Closure $next)
    {
        if ('OPTIONS' === $request->getMethod()) {
            $response = response('');

            return $this->addCorsHeaders($response);
        }

        return $next($request);
    }

    public function addCorsHeaders($response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
