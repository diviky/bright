<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait UserTimezoneAware
{
    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value)
    {
        $timezone = auth()->check() ? auth()->user()->timezone : null;
        $value = parent::asDateTime($value);

        if (is_null($timezone)) {
            return $value;
        }

        return $value->timezone($timezone);
    }
}
