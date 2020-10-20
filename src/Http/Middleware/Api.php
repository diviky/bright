<?php

namespace Diviky\Bright\Http\Middleware;

use Closure;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response = $this->addCorsHeaders($response);

        return $this->respond($response);
    }

    public function addCorsHeaders($response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization,Origin,Accept,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,*');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }

    protected function respond($response)
    {
        $original = $response->getContent();

        if (\is_array($original)) {
            $code = $original['code'];
            if ($code) {
                $response->setStatusCode($code, $original['message']);
            } elseif (\is_numeric($original['status'])) {
                $response->setStatusCode($original['status'], $original['message']);
            }

            unset($original['code']);
            $response->setContent($original);
        }

        return $response;
    }
}
