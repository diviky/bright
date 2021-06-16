<?php

declare(strict_types=1);

namespace Diviky\Bright\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json([
                'status' => 401,
                'message' => $exception->getMessage(),
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
