<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

use Carbon\Exceptions\InvalidFormatException;
use Diviky\Bright\Contracts\UtilInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Util implements UtilInterface
{
    /**
     * Convert given date to carbon supported date format.
     *
     * @param  null|Carbon|int|string  $time
     * @param  null|string  $format
     * @param  null|string  $timezone
     * @return \Illuminate\Support\Carbon|string
     */
    public function carbon($time = null, $format = null, $timezone = null)
    {
        if (\is_null($timezone)) {
            $timezone = user('timezone') ?: config('app.timezone');
        }

        if (!isset($time) && !isset($format)) {
            return (new Carbon)->setTimezone($timezone);
        }

        if (isset($time) && !\is_numeric($time) && is_string($time)) {
            $parts = \explode('/', $time);

            if ($parts[0] && \strlen($parts[0]) != 4) {
                $time = \str_replace('/', '-', \trim($time));
            }
            $carbon = new Carbon($time);
        } elseif ($time instanceof Carbon) {
            $carbon = $time;
        } else {
            $carbon = Carbon::createFromTimestamp($time);
        }

        $carbon = $carbon->setTimezone($timezone);

        if (isset($format)) {
            return $carbon->format($format);
        }

        return $carbon;
    }

    /**
     * Convert time to human readle format.
     *
     * @param  mixed  $time
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
     * @param  string|null  $path
     * @param  null|string  $disk
     * @param  null|int  $time
     * @return null|string
     */
    public function disk($path, $disk = null, $time = null)
    {
        if (!isset($path)) {
            return null;
        }

        if (\substr($path, 0, 5) == 'data:') {
            return $path;
        }

        $disk = $disk ?? config('filesystems.default');
        $disk = ($disk == 'local') ? 'public' : $disk;

        if (isset($time) && $disk == 's3') {
            try {
                return Storage::disk($disk)->temporaryUrl($path, Carbon::now()->addMinutes($time));
            } catch (\Exception $e) {
                return Storage::disk($disk)->url($path);
            }
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Helper to get the user details from Logged in User.
     *
     * @param  null|string  $field
     * @return mixed
     */
    public function user($field = null)
    {
        if (isset($field) && $field == 'id' && app()->has('user_id')) {
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
     * @param  null|int|string  $time
     * @param  string  $format
     * @return null|\Illuminate\Support\Carbon|string
     */
    public function datetime($time, $format = 'M d, Y h:i A')
    {
        if (!isset($time)) {
            return null;
        }

        return carbon($time, $format);
    }

    /**
     * Convert give date to hours and minutes.
     *
     * @param  null|int|string  $time
     * @param  string  $format
     * @return null|\Illuminate\Support\Carbon|string
     */
    public function toTime($time, $format = 'h:i A')
    {
        if (isset($time)) {
            return carbon($time, $format);
        }

        return null;
    }

    /**
     * Convert give time to UTC time.
     *
     * @param  null|\DateTimeInterface|string  $date
     * @param  null|\DateTimeZone|string  $timezone
     * @return Carbon
     *
     * @throws InvalidFormatException
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
     * @param  float|int  $value
     * @param  int  $decimals
     * @return int|string
     */
    public function currency($value, $decimals = 4)
    {
        return \number_format($value, $decimals, '.', '');
    }
}
