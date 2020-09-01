<?php

use Carbon\Carbon;
use Karla\View\View;
use Karla\Contracts\Util;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

if (!function_exists('printr')) {
    function printr($data)
    {
        echo '<pre>';
        print_r($data);
    }
}

if (!function_exists('user')) {
    function user($field = null)
    {
        return app(Util::class)->user($field);
    }
}

if (!function_exists('carbon')) {
    function carbon($time = null, $format = null, $timezone = null)
    {
        return app(Util::class)->carbon($time, $format, $timezone);
    }
}

if (!function_exists('ago')) {
    function ago($time)
    {
        return app(Util::class)->ago($time);
    }
}

if (!function_exists('ip')) {
    function ip()
    {
        return \request()->ip();
    }
}

if (!function_exists('markdown')) {
    function markdown($text)
    {
        if (is_null($text)) {
            return null;
        }

        $converter = new CommonMarkConverter([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convertToHtml($text);
    }
}

if (!function_exists('disk')) {
    function disk($path, $disk = null, $time = null)
    {
        return app(Util::class)->disk($path, $disk, $time);
    }
}

if (!function_exists('user_id')) {
    function user_id($column = 'id')
    {
        return user($column);
    }
}

if (!function_exists('uuid')) {
    function uuid()
    {
        return (string) Str::uuid();
    }
}

if (!function_exists('storage_public')) {
    function storage_public()
    {
        return storage_path('app/public/' . $path);
    }
}

function kview($controller, $view, $data = [], $mergeData = [])
{
    $factory = app(View::class);

    return $factory->make($controller, $view, $data, $mergeData);
}

function utcTime($date = null, $timezone = null)
{
    return app(Util::class)->utcTime($date, $timezone);
}
