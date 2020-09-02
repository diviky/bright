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
     * @param \Throwable $e
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
     * @param \Throwable               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        if ($e instanceof Throwable) {
            $view = 'errors.' . $e->getCode();
            if (view()->exists($view)) {
                return response()->view($view, ['exception' => $e]);
            }
        }

        return parent::render($request, $e);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        $response = parent::convertExceptionToArray($e);

        if ($e instanceof Throwable) {
            $response['status'] = $e->getCode();
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $e)
    {
        $format = $request->input('format');

        if ('json' == $format || $request->expectsJson()) {
            return response()->json(['status' => 401, 'message' => $e->getMessage()], 401);
        }

        return redirect()->guest(route('login'));
    }
}
