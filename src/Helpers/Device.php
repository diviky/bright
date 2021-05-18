<?php

namespace Diviky\Bright\Helpers;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\OperatingSystem;
use DeviceDetector\Yaml\Symfony;
use Illuminate\Support\Arr;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Device
{
    public function detect(?string $userAgent = null, bool $advanced = false, bool $bot = false): array
    {
        $userAgent = $userAgent ?: env('HTTP_USER_AGENT');

        $dd = app(DeviceDetector::class);

        $dd->setUserAgent($userAgent);
        $dd->skipBotDetection();
        $dd->setYamlParser(new Symfony());
        $dd->parse();

        $return = [];
        if ($bot && $dd->isBot()) {
            $return['bot'] = $dd->getBot();

            return $return;
        }

        //device wrapper
        $devicelist = [
            'desktop'               => 'computer',
            'smartphone'            => 'phone',
            'tablet'                => 'tablet',
            'feature phone'         => 'phone',
            'phablet'               => 'phone',
            'console'               => 'phone',
            'tv'                    => 'tablet',
            'car browser'           => 'tablet',
            'smart display'         => 'tablet',
            'camera'                => 'tablet',
            'portable media player' => 'phone',
        ];

        $device = $dd->getDeviceName();
        $type   = (isset($devicelist[$device])) ? $devicelist[$device] : 'computer';

        $os     = $dd->getOs();
        $os     = !is_array($os) ? [] : $os;

        $client     = $dd->getClient();
        $client     = !is_array($client) ? [] : $client;

        //legacy params
        $return['device']          = $device;
        $return['type']            = $type;
        $return['brand']           = $dd->getBrandName();
        $return['os']              = Arr::get($os, 'name');
        $return['os_version']      = Arr::get($os, 'version');
        $return['os_code']         = Arr::get($os, 'short_name');
        $return['browser']         = Arr::get($client, 'name');
        $return['browser_version'] = Arr::get($client, 'version');
        $return['browser_code']    = Arr::get($client, 'short_name');
        $return['browser_type']    = Arr::get($client, 'type');
        $return['browser_engine']  = Arr::get($client, 'engine');

        if (!$advanced) {
            return \array_map('trim', $return);
        }

        //advanced params
        $osFamily            = OperatingSystem::getOsFamily($os['short_name']);
        $return['os_family'] = (false !== $osFamily) ? $osFamily : 'Unknown';

        $return['model'] = $dd->getModel();

        $browserFamily            = Browser::getBrowserFamily($client['short_name']);
        $return['browser_family'] = (false !== $browserFamily) ? $browserFamily : 'Unknown';

        $return['touch'] = $dd->isTouchEnabled();

        unset($os, $client, $osFamily, $browserFamily);

        return $return;
    }
}
