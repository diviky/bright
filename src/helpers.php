<?php

declare(strict_types=1);

use Diviky\Bright\Contracts\UtilInterface as Util;
use Diviky\Bright\View\View;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

if (!function_exists('user')) {
    /**
     * Helper to get the user details from Logged in User.
     *
     * @param null|string $field
     *
     * @return mixed
     */
    function user($field = null)
    {
        return app(Util::class)->user($field);
    }
}

if (!function_exists('carbon')) {
    /**
     * Convert given date to carbon supported date format.
     *
     * @param null|int|string $time
     * @param null|string     $format
     * @param null|string     $timezone
     *
     * @return \Illuminate\Support\Carbon|string
     */
    function carbon($time = null, $format = null, $timezone = null)
    {
        return app(Util::class)->carbon($time, $format, $timezone);
    }
}

if (!function_exists('ago')) {
    /**
     * Convert time to human readle format.
     *
     * @param mixed $time
     *
     * @return null|string
     */
    function ago($time)
    {
        return app(Util::class)->ago($time);
    }
}

if (!function_exists('ip')) {
    /**
     * Get the user Ip address.
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return null|string
     */
    function ip()
    {
        $request = \request();

        return $request ? $request->ip() : null;
    }
}

if (!function_exists('markdown')) {
    /**
     * Converts CommonMark to HTML.
     *
     * @param null|string $text
     *
     * @throws \RuntimeException
     *
     * @return null|string
     */
    function markdown($text)
    {
        if (is_null($text)) {
            return null;
        }

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($text);
    }
}

if (!function_exists('disk')) {
    /**
     * Convert given string to Storage url.
     *
     * @param null|string $path
     * @param null|string $disk
     * @param null|int    $time
     *
     * @return null|string
     */
    function disk($path, $disk = null, $time = null)
    {
        return app(Util::class)->disk($path, $disk, $time);
    }
}

if (!function_exists('user_id')) {
    /**
     * Get user id from logged in user.
     *
     * @param string $column
     *
     * @return null|int|string
     */
    function user_id($column = 'id')
    {
        return user($column);
    }
}

if (!function_exists('uuid')) {
    /**
     * Generate a UUID (version 4).
     */
    function uuid(): string
    {
        return (string) Str::uuid();
    }
}

if (!function_exists('storage_public')) {
    /**
     * Short had method to public path.
     *
     * @return string
     */
    function storage_public()
    {
        return storage_path('app/public/');
    }
}

/**
 * Create view from controller location.
 *
 * @param Illuminate\Routing\Controller|string $controller
 * @param string                               $view
 * @param mixed                                $data
 * @param mixed                                $mergeData
 *
 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
 */
function kview($controller, $view, $data = [], $mergeData = [])
{
    $factory = app(View::class);

    return $factory->make($controller, $view, $data, $mergeData);
}

/**
 * Convert date to UTC time.
 *
 * @param null|string $date
 * @param null|string $timezone
 *
 * @return Illuminate\Support\Carbon
 */
function utcTime($date = null, $timezone = null)
{
    return app(Util::class)->utcTime($date, $timezone);
}

/**
 * Respond with json.
 *
 * @param mixed $data
 *
 * @deprecated 2.0
 *
 * @return \Illuminate\Http\JsonResponse
 */
function json($data)
{
    return response()->json($data);
}
