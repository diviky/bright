<?php

namespace Diviky\Bright\Util;

use Diviky\Bright\Contracts\UtilInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Util implements UtilInterface
{
    /**
     * Convert given date to carbon supported date format.
     *
     * @param null|int|string $time
     * @param null|string     $format
     * @param null|string     $timezone
     *
     * @return \Illuminate\Support\Carbon|string
     */
    public function carbon($time = null, $format = null, $timezone = null)
    {
        if (\is_null($timezone)) {
            $timezone = user('timezone') ?: config('app.timezone');
        }

        if (empty($time) && empty($format)) {
            return (new Carbon())->setTimezone($timezone);
        }

        if (!\is_numeric($time)) {
            $parts = \explode('/', $time);

            if ($parts[0] && 4 != \strlen($parts[0])) {
                $time = \str_replace('/', '-', \trim($time));
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

    /**
     * Convert time to human readle format.
     *
     * @param mixed $time
     *
     * @return null|string
     */
    public function ago($time)
    {
        if (empty($time)) {
            return null;
        }

        return $this->carbon($time)->diffForHumans();
    }

    /**
     * Convert given string to Storage url.
     *
     * @param null|string $path
     * @param null|string $disk
     * @param null|int    $time
     *
     * @return null|string
     */
    public function disk($path, $disk = null, $time = null)
    {
        if (empty($path)) {
            return null;
        }

        if ('data:' == \substr($path, 0, 5)) {
            return $path;
        }

        $disk = $disk ?: config('filesystems.default');
        $disk = ('local' == $disk) ? 'public' : $disk;

        if ($time) {
            try {
                return Storage::disk($disk)->temporaryUrl($path, Carbon::now()->addMinutes($time));
            } catch (\Exception $e) {
                Log::error((string) $e);
            }
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Helper to get the user details from Logged in User.
     *
     * @param null|string $field
     *
     * @return mixed
     */
    public function user($field = null)
    {
        if (isset($field) && 'id' == $field && app()->has('user_id')) {
            return app()->get('user_id');
        }

        $user = Auth::user();
        if (isset($field)) {
            return $user ? $user->{$field} : null;
        }

        return $user;
    }

    /**
     * Convert give date to human reable format.
     *
     * @param null|int|string $time
     * @param string          $format
     *
     * @return null|\Illuminate\Support\Carbon|string
     */
    public function datetime($time, $format = 'M d, Y h:i A')
    {
        if (empty($time)) {
            return null;
        }

        return carbon($time, $format);
    }

    /**
     * Convert give date to hours and minutes.
     *
     * @param null|int|string $time
     * @param string          $format
     *
     * @return null|\Illuminate\Support\Carbon|string
     */
    public function toTime($time, $format = 'h:i A')
    {
        if ($time) {
            return carbon($time, $format);
        }

        return null;
    }

    /**
     * Convert give time to UTC time.
     *
     * @param null|int|string $date
     * @param null|string     $timezone
     *
     * @return \Illuminate\Support\Carbon
     */
    public function utcTime($date = null, $timezone = null)
    {
        if (\is_null($timezone)) {
            $timezone = user('timezone') ?: config('app.timezone');
        }

        return Carbon::parse($date, $timezone)->setTimezone('UTC');
    }

    /**
     * Convert number to decimal point.
     *
     * @param float|int $value
     * @param int       $decimals
     *
     * @return int|string
     */
    public function currency($value, $decimals = 4)
    {
        return \number_format($value, $decimals, '.', '');
    }
}
