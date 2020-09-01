<?php

namespace Karla\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
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
     * @param \Throwable               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof Throwable) {
            $view = 'errors.' . $exception->getCode();
            if (view()->exists($view)) {
                return response()->view($view, ['exception' => $exception]);
            }
        }

        return parent::render($request, $exception);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $response = parent::convertExceptionToArray($e);

        if ($e instanceof Throwable) {
            $response['status'] = $e->getCode();
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json(['status' => 401, 'message' => $exception->getMessage()], 401);
        }

        return redirect()->guest(route('login'));
    }
}
