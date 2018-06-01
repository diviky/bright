<?php

use Carbon\Carbon;

if (!function_exists('printr')) {
    function printr($data)
    {
        echo '<pre>';
        print_r($data);
    }
}

if (!function_exists('ago')) {
    function ago($time) {
        if (empty($time)) {
            return null;
        }

        return (new Carbon($time))->diffForHumans();
    }
}

if (!function_exists('toDate')) {
    function toDate($time, $format = null) {
        if (empty($time)) {
            return null;
        }

        $format = $format ?: 'M d, Y h:i A';
        $timestamp = is_numeric($time) ? $time : strtotime($time);

        return date($format, $timestamp);
    }
}