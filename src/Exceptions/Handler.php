<?php

namespace Karla\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        if (!config('app.debug') && app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof Exception) {
            $view = 'errors.'.$exception->getCode();
            if (view()->exists($view)) {
                return response()->view($view, ['exception' => $exception]);
            }
        }

        return parent::render($request, $exception);
    }

    protected function convertExceptionToArray(Exception $e)
    {
        $response = parent::convertExceptionToArray($e);

        if ($e instanceof Exception) {
            $response['status'] = $e->getCode();
        }

        return $response;
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
        ? response()->json(['status' => 401, 'message' => $exception->getMessage()], 401)
        : redirect()->guest(route('login'));
    }
}
