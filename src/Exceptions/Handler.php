<?php

declare(strict_types=1);

namespace Diviky\Bright\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    #[\Override]
    public function register(): void
    {
        $this->reportable(function (\Throwable $e): void {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $format = $request->input('format');

        if ($format == 'json' || $this->shouldReturnJson($request, $exception)) {
            return response()->json([
                'status' => 'ERROR',
                'code' => 401,
                'message' => $exception->getMessage(),
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    #[\Override]
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => 'ERROR',
            'code' => 422,
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    #[\Override]
    protected function convertExceptionToArray(\Throwable $e)
    {
        if (config('app.debug')) {
            return [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
            ];
        }

        if ($e->getPrevious() instanceof ModelNotFoundException) {
            $message = 'No results found';
        } else {
            $message = $e->getMessage();
        }

        return [
            'status' => 'ERROR',
            'code' => $this->isHttpException($e) ? $e->getStatusCode() : 500,
            'message' => $this->isHttpException($e) ? $message : 'Server Error',
        ];
    }
}
