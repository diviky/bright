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
    public function report(Throwable $e): void
    {
        if (!config('app.debug') && app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * {@inheritDoc}
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json([
                'status'  => 401,
                'message' => $exception->getMessage(),
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}
