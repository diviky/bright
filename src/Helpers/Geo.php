<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\GeoIP2\GeoIP2;
use Geocoder\Provider\GeoIP2\GeoIP2Adapter;
use Geocoder\ProviderAggregator;
use GeoIp2\Database\Reader;
use Illuminate\Support\Arr;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Geo
{
    /**
     * @param string $address
     * @param string $db
     *
     * @return string[]
     */
    public function geocode($address, $db = 'GeoLite2-City.mmdb'): array
    {
        $path = config('bright.geoip.database_path');

        $db = $path . '/' . $db;

        if (!file_exists($db)) {
            return [];
        }

        //http://ipinfo.io/119.63.142.37/json

        $geocoder = new ProviderAggregator();

        $reader = new Reader($db);
        $geoIP2Adapter = new GeoIP2Adapter($reader);

        $chain = new Chain([
            new GeoIP2($geoIP2Adapter),
        ]);

        $geocoder->registerProvider($chain);

        $results = false;

        try {
            $results = $geocoder->geocode($address);
        } catch (\Exception $e) {
            $results = false;
        }

        $result = [];
        if (false !== $results) {
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
        }

        return \array_map('trim', $result);
    }
}
