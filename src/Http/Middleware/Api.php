<?php

namespace Karla\Http\Middleware;

use Closure;

class Api
{
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
        $request->headers->add([
            'Accept' => 'application/json',
        ]);

        app()->is_api_request = true;

        $response = $next($request);
        $response = $this->addCorsHeaders($response);

        return $this->respond($response);
    }

    protected function respond($response)
    {
        $original = $response->getOriginalContent();
        if (is_array($original)) {
            $code = $original['code'];
            if ($code) {
                $response->setStatusCode($code, $original['message']);
            }
        }

        return $response;
    }

    public function addCorsHeaders($response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Max-Age', 1000);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
