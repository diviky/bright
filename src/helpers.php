<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            return new Carbon;
        }

        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && strlen($parts[0]) != 4) {
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
            return null;
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
