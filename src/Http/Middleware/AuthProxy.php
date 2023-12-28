<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Routing\Pipeline;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthProxy
{
    /**
     * Handle the incoming requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return (new Pipeline(app()))->send($request)->through(static::middlewares($request))->then(function ($request) use ($next): mixed {
            return $next($request);
        });
    }

    /**
     * Determine if the given request has JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public static function middlewares($request): array
    {
        $token = static::getAccessToken($request);

        if (empty($token)) {
            if ($request->hasHeader('X-SPA')) {
                return [EnsureFrontendRequestsAreStateful::class, Authenticate::class . ':web'];
            }

            return [Authenticate::class . ':token'];
        }

        $re = '/^[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.[A-Za-z0-9-_\.+=]*$/m';

        if (preg_match($re, $token)) {
            return [Authenticate::class . ':jwt'];
        }

        return [Authenticate::class . ':token'];
    }

    /**
     * Determine if the given request has JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return null|mixed|string
     */
    public static function getAccessToken($request)
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            $token = $request->header('Authorization');
        }

        if (empty($token)) {
            $inputKeys = ['access_token', 'api_token'];
            foreach ($inputKeys as $key) {
                $token = $request->query($key);
                if (empty($token)) {
                    $token = $request->post($key);
                }

                if ($token) {
                    return $token;
                }
            }
        }

        return $token;
    }
}
