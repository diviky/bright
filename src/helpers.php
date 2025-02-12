<?php

declare(strict_types=1);

use Diviky\Bright\Contracts\UtilInterface as Util;
use Diviky\Bright\Util\StdClass;
use Diviky\Bright\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

if (!function_exists('user')) {
    /**
     * Helper to get the user details from Logged in User.
     *
     * @param  null|string  $field
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
     * @param  null|int|string  $time
     * @param  null|string  $format
     * @param  null|string  $timezone
     * @return Illuminate\Support\Carbon|string
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
     * @param  mixed  $time
     * @return null|string
     */
    function ago($time)
    {
        return app(Util::class)->ago($time);
    }
}

if (!function_exists('disk')) {
    /**
     * Convert given string to Storage url.
     *
     * @param  null|string  $path
     * @param  null|string  $disk
     * @param  null|int  $minutes
     * @return null|string
     */
    function disk($path, $disk = null, $minutes = null)
    {
        return app(Util::class)->disk($path, $disk, $minutes);
    }
}

if (!function_exists('user_id')) {
    /**
     * Get user id from logged in user.
     *
     * @param  string  $column
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
 * @param  Illuminate\Routing\Controller|string  $controller
 * @param  string  $view
 * @param  mixed  $data
 * @param  mixed  $mergeData
 * @return Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
 */
function kview($controller, $view, $data = [], $mergeData = [])
{
    $factory = app(View::class);

    return $factory->make($controller, $view, $data, $mergeData);
}

/**
 * Convert date to UTC time.
 *
 * @param  null|string  $date
 * @param  null|string  $timezone
 * @return Illuminate\Support\Carbon
 */
function utcTime($date = null, $timezone = null)
{
    return app(Util::class)->utcTime($date, $timezone);
}

/**
 * @param  \Illuminate\Contracts\Support\Arrayable|iterable|array|null  $items
 * @param  mixed  $default
 */
function collects($items = [], $default = null): Collection
{
    return new StdClass($items, $default);
}
