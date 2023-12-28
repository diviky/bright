<?php

declare(strict_types=1);

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
    public function detect(string $userAgent, bool $advanced = false, bool $check_bot = false): array
    {
        $detector = $this->getDetector($userAgent);

        $return = [];
        if ($check_bot && $detector->isBot()) {
            $return['bot'] = $detector->getBot();

            return $return;
        }

        // device wrapper
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

        $device = $detector->getDeviceName();
        $type = (isset($devicelist[$device])) ? $devicelist[$device] : 'computer';

        $system = $detector->getOs();
        $system = !is_array($system) ? [] : $system;

        $client = $detector->getClient();
        $client = !is_array($client) ? [] : $client;

        // legacy params
        $return['device'] = $device;
        $return['type'] = $type;
        $return['brand'] = $detector->getBrandName();
        $return['os'] = Arr::get($system, 'name');
        $return['os_version'] = Arr::get($system, 'version');
        $return['os_code'] = Arr::get($system, 'short_name');
        $return['browser'] = Arr::get($client, 'name');
        $return['browser_version'] = Arr::get($client, 'version');
        $return['browser_code'] = Arr::get($client, 'short_name');
        $return['browser_type'] = Arr::get($client, 'type');
        $return['browser_engine'] = Arr::get($client, 'engine');

        if (!$advanced) {
            return \array_map('trim', $return);
        }

        $return['os_family'] = 'Unknown';
        $return['browser_family'] = 'Unknown';

        // advanced params
        if (isset($system['short_name'])) {
            $osFamily = OperatingSystem::getOsFamily($system['short_name']);
            $return['os_family'] = ($osFamily !== false) ? $osFamily : 'Unknown';
        }

        $return['model'] = $detector->getModel();

        if (isset($client['short_name'])) {
            $browserFamily = Browser::getBrowserFamily($client['short_name']);
            $return['browser_family'] = ($browserFamily !== false) ? $browserFamily : 'Unknown';
        }

        $return['touch'] = $detector->isTouchEnabled();

        unset($system, $client, $osFamily, $browserFamily);

        return $return;
    }

    /**
     * Get the detector class.
     *
     * @return DeviceDetector
     */
    protected function getDetector(string $userAgent)
    {
        $detector = app(DeviceDetector::class);

        $detector->setUserAgent($userAgent);
        $detector->setYamlParser(new Symfony());
        $detector->parse();

        return $detector;
    }
}
