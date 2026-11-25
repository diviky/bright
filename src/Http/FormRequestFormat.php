<?php

declare(strict_types=1);

namespace Diviky\Bright\Http;

use Illuminate\Http\Request;

class FormRequestFormat
{
    public static function inputFormat(Request $request): ?string
    {
        /** @var null|string $format */
        $format = $request->input('_request', $request->input('format', $request->input('_format')));

        return is_string($format) ? $format : null;
    }

    public static function wantsJsonResponse(Request $request): bool
    {
        if ($request->pjax() || $request->header('X-Inertia')) {
            return false;
        }

        if ($request->ajax()) {
            return true;
        }

        return self::inputFormat($request) === 'json';
    }

    public static function prepare(Request $request): void
    {
        if (self::inputFormat($request) !== 'json') {
            return;
        }

        $request->headers->set('Accept', 'application/json');

        if (! $request->headers->has('X-Requested-With')) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        }
    }

    public static function toAbsoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}