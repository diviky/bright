<?php

namespace Karla\Listeners\Traits;

use Karla\Helpers\Device as BaseDevice;
use Karla\Helpers\Geo;

trait Device
{
    protected function getDeviceDetails($ip, $userAgent): array
    {
        $device  = new BaseDevice();
        $details = (array) $device->detect($userAgent, true);

        $geoHelper = new Geo();
        $geo       = (array) $geoHelper->geocode($ip);

        return [
            'country'      => $geo['country'],
            'country_code' => $geo['country_code'],
            'region'       => $geo['region'],
            'city'         => $geo['city'],
            'os'           => $details['os'],
            'browser'      => $details['browser'],
            'device'       => $details['device'],
            'brand'        => $details['brand'],
        ];
    }
}
