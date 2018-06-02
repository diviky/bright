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

if (!function_exists('ago')) {
    function ago($time)
    {
        if (empty($time)) {
            return null;
        }

        return (new Carbon($time))->diffForHumans();
    }
}

if (!function_exists('toDate')) {
    function toDate($time, $format = null)
    {
        if (empty($time)) {
            return null;
        }

        $format = $format ?: 'M d, Y h:i A';
        $timestamp = is_numeric($time) ? $time : strtotime($time);

        return date($format, $timestamp);
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

if (!function_exists('toTime')) {
    function toTime($time, $date = false, $format = 'Y-m-d')
    {
        if (!is_numeric($time)) {
            $parts = explode('/', $time);

            if ($parts[0] && strlen($parts[0]) != 4) {
                $time = str_replace('/', '-', trim($time));
            }

            $time = strtotime($time);
        }

        if ($date) {
            return date($format, $time);
        }

        return $time;
    }
}

if (!function_exists('ip')) {
    function ip()
    {
        return \request()->ip();
    }
}
