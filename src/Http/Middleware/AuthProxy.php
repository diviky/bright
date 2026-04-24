<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Pipeline;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthProxy
{
    /**
     * Handle the incoming requests.
     *
     * @param  Request  $request
     * @param  callable  $next
     * @return Response
     */
    public function handle($request, $next)
    {
        static::normalizeBasicAuthWithEmptyPasswordToBearer($request);

        return (new Pipeline(app()))
            ->send($request)
            ->through(static::middlewares($request))
            ->then(function ($request) use ($next): mixed {
                return $next($request);
            });
    }

    /**
     * Determine if the given request has JWT token.
     *
     * @param  Request  $request
     */
    public static function middlewares($request): array
    {
        $token = static::getAccessToken($request);

        if (empty($token)) {
            if ($request->hasHeader('X-SPA')) {
                return [EnsureFrontendRequestsAreStateful::class, Authenticate::class . ':web'];
            }

            return [Authenticate::class . ':web'];
        }

        if (preg_match('/^\s*Basic\s+(\S+)\s*$/i', $token, $matches)) {
            return [AuthenticateOnceWithBasicAuth::class];
        }

        $re = '/^[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.[A-Za-z0-9-_\.+=]*$/m';

        if (preg_match($re, $token)) {
            return [Authenticate::class . ':jwt'];
        }

        return [Authenticate::class . ':access_token'];
    }

    /**
     * Determine if the given request has JWT token.
     *
     * @param  Request  $request
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

    /**
     * When Authorization is Basic and the password segment is empty, treat the
     * username as an API token by rewriting the header to Bearer.
     *
     * @param  Request  $request
     */
    private static function normalizeBasicAuthWithEmptyPasswordToBearer($request): void
    {
        $authorization = $request->header('Authorization');
        if ($authorization === null || $authorization === '') {
            return;
        }

        if (!preg_match('/^\s*Basic\s+(\S+)\s*$/i', $authorization, $matches)) {
            return;
        }

        $decoded = base64_decode($matches[1], true);
        if ($decoded === false) {
            return;
        }

        $colonPosition = strpos($decoded, ':');
        if ($colonPosition === false) {
            $username = $decoded;
            $password = '';
        } else {
            $username = substr($decoded, 0, $colonPosition);
            $password = substr($decoded, $colonPosition + 1);
        }

        if ($password !== '') {
            return;
        }

        if ($username === '') {
            return;
        }

        $request->headers->set('Authorization', 'Bearer ' . $username);
    }
}
