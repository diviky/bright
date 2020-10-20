<?php

namespace Diviky\Bright\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     */
    public function report(Throwable $e)
    {
        if (!config('app.debug') && app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $response = parent::convertExceptionToArray($e);

        if ($e instanceof Throwable && !isset($response['status'])) {
            $response['status'] = $this->getStatusCode($e) ?: 500;
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $e)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json([
                'status'  => 401,
                'message' => $e->getMessage(),
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function getStatusCode($e)
    {
        return $this->isHttpException($e) ? $e->getStatusCode() : $e->getCode();
    }
}
