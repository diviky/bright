<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Illuminate\Support\Arr;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class GeoCode
{
    /**
     * @param string $address
     *
     * @return string[]
     *
     * @psalm-return array{provider?: string, latitude?: string, longitude?: string, country?: string, country_code?: string, city?: string, region?: string, region_code?: string, zipcode?: string, locality?: string, timezone?: string}
     */
    public function geocode($address): array
    {
        $results = app('geocoder')->geocode($address)->get();

        try {
            $results = app('geocoder')->geocode($address)->get();
        } catch (\Exception $e) {
            return [];
        }

        $result = [];
        foreach ($results as $value) {
            try {
                $region = $value->getAdminLevels()->get(1);
            } catch (\Exception $e) {
                $region = false;
            }

            $data = $value->toArray();

            $result['provider'] = Arr::get($data, 'providedBy');
            $result['latitude'] = Arr::get($data, 'latitude');
            $result['longitude'] = Arr::get($data, 'longitude');
            $result['country'] = Arr::get($data, 'country');
            $result['country_code'] = Arr::get($data, 'countryCode');
            $result['city'] = Arr::get($data, 'locality');
            $result['region'] = ($region) ? $region->getName() : '';
            $result['region_code'] = ($region) ? $region->getCode() : '';
            $result['zipcode'] = Arr::get($data, 'postalCode');
            $result['locality'] = Arr::get($data, 'locality');
            $result['timezone'] = Arr::get($data, 'timezone');
        }

        return \array_map('trim', $result);
    }
}
