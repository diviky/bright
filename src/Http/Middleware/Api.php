<?php

namespace Diviky\Bright\Http\Middleware;

use Closure;

class Api
{
    protected $keep_code = true;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $keep_code
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $keep_code = true)
    {
        $this->keep_code = $keep_code;
        $response        = $next($request);
        $response        = $this->addCorsHeaders($response);

        return $this->respond($response);
    }

    public function addCorsHeaders($response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization,Origin,Accept,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,X-Auth-Method,X-Auth-Nonce,X-Auth-Date,*');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }

    protected function respond($response)
    {
        $original = $response->getOriginalContent();

        if (\is_array($original)) {
            $code = $original['code'];

            if ($code && \is_numeric($code)) {
                if (200 == $code) {
                    $response->setStatusCode($code, 'OK');
                } else {
                    $response->setStatusCode($code, $original['message'] ?? 'OK');
                }
            } elseif (\is_numeric($original['status'])) {
                $response->setStatusCode($original['status'], $original['message'] ?? 'OK');
            }

            if (!$this->keep_code) {
                unset($original['code']);
            }

            $response->setContent(\json_encode($original));
        }

        return $response;
    }
}
