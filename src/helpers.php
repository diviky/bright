<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Karla\View\View;

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
        if ('id' == $field && app()->has('user_id')) {
            return app()->get('user_id');
        }

        if ($field) {
            return Auth::user()->$field;
        }

        return Auth::user();
    }
}

if (!function_exists('carbon')) {
    function carbon($time = null, $format = null)
    {
        if (empty($time) && empty($format)) {
            return new Carbon();
        }

        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && 4 != strlen($parts[0])) {
                $time = str_replace('/', '-', trim($time));
            }
            $carbon = new Carbon($time);
        } else {
            $carbon = Carbon::createFromTimestamp($time);
        }

        if ($format) {
            return $carbon->format($format);
        }

        return $carbon;
    }
}

if (!function_exists('ago')) {
    function ago($time)
    {
        if (empty($time)) {
            return;
        }

        return carbon($time)->diffForHumans();
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
        $parsedown = new Parsedown();

        return $parsedown->text($text);
    }
}

if (!function_exists('disk')) {
    function disk($path, $disk = null, $time = null)
    {
        if (empty($path)) {
            return;
        }

        if ('data:' == substr($path, 0, 5)) {
            return $path;
        }

        $disk = $disk ?: config('filesystems.default');
        $disk = ('local' == $disk) ? 'public' : $disk;

        if ($time) {
            try {
                return Storage::disk($disk)->temporaryUrl($path, Carbon::now()->addMinutes($time));
            } catch (\Exception $e) {

            }
        }

        return Storage::disk($disk)->url($path);
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
