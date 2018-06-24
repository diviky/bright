<?php

namespace Karla\Helpers;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\Parser\OperatingSystem;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Device
{
    public function detect($userAgent = null, $advanced = false)
    {
        $userAgent = $userAgent ?: env('HTTP_USER_AGENT');
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
        $detect = new DeviceDetector($userAgent);
        $detect->parse();

        $return = [];
        if ($detect->isBot()) {
            $return['bot'] = $detect->getBot();

            return $return;
        }

        //device wrapper
        $devicelist = [
            'desktop' => 'computer',
            'smartphone' => 'phone',
            'tablet' => 'tablet',
            'feature phone' => 'phone',
            'phablet' => 'phone',
            'console' => 'phone',
            'tv' => 'tablet',
            'car browser' => 'tablet',
            'smart display' => 'tablet',
            'camera' => 'tablet',
            'portable media player' => 'phone',
        ];

        $devicename = $detect->getDeviceName();
        $devicetype = (isset($devicelist[$devicename])) ? $devicelist[$devicename] : 'computer';

        $os = $detect->getOs();
        $client = $detect->getClient();

        //legacy params
        $return['device'] = $devicename;
        $return['type'] = $devicetype;
        $return['brand'] = $detect->getBrandName();
        $return['os'] = $os['name'];
        $return['os_version'] = $os['version'];
        $return['os_code'] = $os['short_name'];
        $return['browser'] = $client['name'];
        $return['browser_version'] = $client['version'];
        $return['browser_code'] = $client['short_name'];
        $return['browser_type'] = $client['type'];
        $return['browser_engine'] = $client['engine'];

        if (!$advanced) {
            return array_map('trim', $return);
        }

        //advanced params
        $osFamily = OperatingSystem::getOsFamily($os['short_name']);
        $return['os_family'] = ($osFamily !== false) ? $osFamily : 'Unknown';

        $return['model'] = $detect->getModel();

        $browserFamily = Browser::getBrowserFamily($client['short_name']);
        $return['browser_family'] = ($browserFamily !== false) ? $browserFamily : 'Unknown';

        $touch = $detect->isTouchEnabled();
        $return['touch'] = $touch[0];

        unset($os, $client, $osFamily, $browserFamily, $touch);

        return array_map('trim', $return);
    }
}
