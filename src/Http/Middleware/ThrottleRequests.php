<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseRequest;

class ThrottleRequests extends BaseRequest
{
    /**
     * Create a 'too many attempts' exception.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $key
     * @param int                      $maxAttempts
     * @param null|callable            $responseCallback
     *
     * @return \Illuminate\Http\Exceptions\HttpResponseException|\Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        if ($retryAfter) {
            $message = 'Too Many Attempts. You may try again after ' . round($retryAfter / 60) . ' minutes.';
        } else {
            $message = 'Too Many Attempts.';
        }

        return is_callable($responseCallback)
                    ? new HttpResponseException($responseCallback($request, $headers))
                    : new ThrottleRequestsException($message, null, $headers);
    }

    /**
     * Resolve request signature.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function resolveRequestSignature($request)
    {
        $user = $request->user();
        if ($user) {
            return sha1((string) $user->getAuthIdentifier());
        }

        return $request->fingerprint();
    }
}
