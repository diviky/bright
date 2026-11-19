<?php

declare(strict_types=1);

namespace Diviky\Bright\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @psalm-mutation-free
     */
    public static function modelNotFoundMessage(ModelNotFoundException $exception): string
    {
        $ids = $exception->getIds();

        if ($ids === []) {
            return 'No query results.';
        }

        return 'No query results for ' . implode(', ', array_map(strval(...), $ids));
    }

    #[\Override]
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $exception, Request $_request) {
            return response()->json([
                'message' => static::modelNotFoundMessage($exception),
            ], Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (NotFoundHttpException $exception, Request $_request) {
            $previous = $exception->getPrevious();

            if (!$previous instanceof ModelNotFoundException) {
                return null;
            }

            return response()->json([
                'message' => static::modelNotFoundMessage($previous),
            ], Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired or invalid CSRF token. Please refresh and try again.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return back()
                ->withInput($request->except('_token'))
                ->with('error', 'Your session expired. Please refresh and try again.');
        });
    }

    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $format = $request->input('format');

        if ($format === 'json' || $this->shouldReturnJson($request, $exception)) {
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
    protected function convertExceptionToArray(Throwable $e)
    {
        $modelNotFoundMessage = $this->resolveModelNotFoundMessage($e);

        if (config('app.debug')) {
            return [
                'message' => $modelNotFoundMessage ?? $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(fn ($trace) => Arr::except($trace, ['args']))->all(),
            ];
        }

        if ($modelNotFoundMessage !== null) {
            $message = $modelNotFoundMessage;
        } else {
            $message = $this->isHttpException($e) ? $e->getMessage() : 'Server Error';
        }

        return [
            'status' => 'ERROR',
            'code' => $this->isHttpException($e) ? $e->getStatusCode() : 500,
            'message' => $message,
        ];
    }

    /**
     * @psalm-mutation-free
     */
    protected function resolveModelNotFoundMessage(Throwable $exception): ?string
    {
        if ($exception instanceof ModelNotFoundException) {
            return static::modelNotFoundMessage($exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            $previous = $exception->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                return static::modelNotFoundMessage($previous);
            }
        }

        return null;
    }
}
