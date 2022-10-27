<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class Api
{
    /**
     * Add error code to response.
     *
     * @var bool
     */
    protected $keep_code = true;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param bool                     $keep_code
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle($request, Closure $next, $keep_code = true)
    {
        $this->keep_code = $keep_code;
        $response = $next($request);
        $response = $this->addCorsHeaders($response);

        return $this->respond($response);
    }

    /**
     * Add cors headers.
     *
     * @param mixed $response
     *
     * @return \Illuminate\Http\Response
     */
    public function addCorsHeaders($response)
    {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type,Authorization,Origin,Accept,Access-Control-Allow-Headers,Access-Control-Allow-Methods,Access-Control-Allow-Origin,X-Auth-Method,X-Auth-Nonce,X-Auth-Date,*');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }

    /**
     * Add cors headers.
     *
     * @param mixed $response
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    protected function respond($response)
    {
        if (!$response instanceof Response && !$response instanceof JsonResponse) {
            return $response;
        }

        $original = $response->getOriginalContent();

        if (\is_array($original)) {
            if (isset($original['errors'])) {
                $original['code'] = 422;
                $original['status'] = 'ERROR';
            }

            if (isset($original['code'])) {
                $code = $original['code'];

                if (!isset($original['data'])) {
                    $original['data'] = [];
                }

                if ($code && \is_numeric($code)) {
                    if (200 == $code) {
                        $response->setStatusCode((int) $code, 'OK');
                    } else {
                        $response->setStatusCode((int) $code, $original['message'] ?? 'OK');
                    }
                } elseif (\is_numeric($original['status'])) {
                    $response->setStatusCode((int) $original['status'], $original['message'] ?? 'OK');
                }

                if ($original['data'] instanceof LengthAwarePaginator) {
                    $original = array_merge($original, JsonResource::collection($original['data'])->response()->getData(true));
                } elseif ($original['data'] instanceof ResourceCollection) {
                    $original = array_merge($original, $original['data']->response()->getData(true));
                }

                if (!$this->keep_code) {
                    unset($original['code']);
                }

                $response->setContent(\json_encode($original));
            }
        }

        return $response;
    }
}
