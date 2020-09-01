<?php

namespace Karla\Util;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Karla\Contracts\Util as UtilContracts;

class Util implements UtilContracts
{
    public function carbon($time = null, $format = null, $timezone = null)
    {
        if (is_null($timezone)) {
            $timezone = user('timezone') ?: config('app.timezone');
        }

        if (empty($time) && empty($format)) {
            return (new Carbon())->setTimezone($timezone);
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

        $carbon = $carbon->setTimezone($timezone);

        if ($format) {
            return $carbon->format($format);
        }

        return $carbon;
    }

    public function ago($time)
    {
        if (empty($time)) {
            return;
        }

        return $this->carbon($time)->diffForHumans();
    }

    public function disk($path, $disk = null, $time = null)
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

    public function user($field = null)
    {
        if ('id' == $field && app()->has('user_id')) {
            return app()->get('user_id');
        }

        if ($field) {
            return Auth::user()->{$field};
        }

        return Auth::user();
    }

    public function datetime($time, $format = 'M d, Y h:i A')
    {
        if (empty($time)) {
            return null;
        }

        return carbon($time, $format);
    }

    public function totime($time, $format = 'h:i A')
    {
        if ($time) {
            return carbon($time, $format);
        }

        return null;
    }

    public function utcTime($date = null, $timezone = null)
    {
        if (is_null($timezone)) {
            $timezone = user('timezone') ?: config('app.timezone');
        }

        return Carbon::parse($date, $timezone)->setTimezone('UTC');
    }

    public function currency($value, $decimals = 4)
    {
        return number_format($value, $decimals, '.', '');
    }
}
