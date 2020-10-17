<?php

namespace Karla\Exceptions;

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

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return response()->json(
                [
                    'status'  => $this->getStatusCode($e),
                    'message' => $e->getMessage(),
                ],
                $this->getStatusCode($e)
            );
        }

        return parent::render($request, $e);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $response = parent::convertExceptionToArray($e);

        if ($e instanceof Throwable) {
            $response['status'] = $this->getStatusCode($e);
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $e)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json([
                'status'  => $this->getStatusCode($e),
                'message' => $e->getMessage(),
            ], $this->getStatusCode($e));
        }

        return redirect()->guest(route('login'));
    }

    protected function getStatusCode($e)
    {
        return $this->isHttpException($e) ? $e->getStatusCode() : 500;
    }
}
