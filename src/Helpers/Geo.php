<?php

namespace Karla\Helpers;

use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Provider\GeoIP2\GeoIP2;
use Geocoder\Provider\GeoIP2\GeoIP2Adapter;
use Geocoder\Provider\HostIp\HostIp;
use Geocoder\ProviderAggregator;
use GeoIp2\Database\Reader;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Geo
{
    public function geocode($address = null, $db = 'GeoLite2-City.mmdb')
    {
        if (null === $address) {
            $address = ip();
        }

        //$address = '203.109.101.177';

        //http://ipinfo.io/119.63.142.37/json

        $geocoder = new ProviderAggregator();
        $adapter  = new GuzzleAdapter();

        $reader        = new Reader(storage_path('geoip').'/'.$db);
        $geoIP2Adapter = new GeoIP2Adapter($reader);

        $chain = new Chain([
            new GeoIP2($geoIP2Adapter),
            new FreeGeoIp($adapter),
            new HostIp($adapter),
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

                $result['provider']     = $data['providedBy'];
                $result['latitude']     = $data['latitude'];
                $result['longitude']    = $data['longitude'];
                $result['country']      = $data['country'];
                $result['country_code'] = $data['countryCode'];
                $result['city']         = $data['locality'];
                $result['region']       = ($region) ? $region->getName() : '';
                $result['region_code']  = ($region) ? $region->getCode() : '';
                $result['zipcode']      = $data['postalCode'];
                $result['locality']     = $data['locality'];
                $result['timezone']     = $data['timezone'];
            }
        }

        return array_map('trim', $result);
    }
}
