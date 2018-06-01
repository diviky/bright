<?php

namespace Karla\Helpers;

use Exception;
use Illuminate\Support\Carbon;
use Karla\Routing\Capsule;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Sms extends Capsule
{
    public function send(array $data = [])
    {
        $config = $this->config('sms');

        $tags = (is_array($data['tags'])) ? $data['tags'] : [];

        $tags['sitename'] = config('app.name');

        if ($data['template']) {
            $name = strtolower($data['template']);
            $name = str_replace(['.txt'], '', $name);
            $from = $config['from_list'];

            if (is_array($from) && $from[$name]) {
                $data['from'] = $from[$name];
            }

            $data['message'] = $this->getContent($data['template']);
        }

        if (empty($data['from'])) {
            $data['from'] = $config['from'];
        }

        $data['message'] = $this->replace($tags, $data['message']);
        $data['to'] = $this->formatMobileNumber($data['to']);

        //if disable
        if (!$config['enable']) {
            $data['reason'] = 'Message sending disabled';
            $this->logSms($data, false);

            return true;
        }

        if (empty($data['to'])) {
            $data['reason'] = 'Not valid mobile numbers';
            $this->logSms($data, false);

            return true;
        }

        $config['provider'] = $data['provider'] ?: $config['provider'];

        if (empty($config['provider'])) {
            $data['reason'] = 'Provider not found';
            $this->logSms($data, false);

            return true;
        }

        $provider = $config['provider'];
        $providers = $config['providers'];
        $config = $providers[$provider];
        $config = array_merge(['from' => $data['from']], $config);
        $driver = $config['driver'];

        try {
            $sms = $this->get('resolver')->getHelper($driver);
            $sent = $sms->send($data, $config);
        } catch (Exception $e) {
            $sent = [];
            $sent['status'] = 'FAILED';
            $sent['message'] = $e->getMessage();
        }

        $status = $sent['status'];

        if (!$status) {
            $data['reason'] = $sent['message'];
        }

        $this->logSms($data, $status);

        if ($status) {
            return true;
        }

        return false;
    }

    private function formatMobileNumber($mobile, $blacklist = true)
    {
        if (empty($mobile)) {
            return [];
        }

        if (!is_array($mobile)) {
            if (preg_match('/,[^\S]*/', $mobile)) {
                $mobile = explode(',', $mobile);
            } else {
                $mobile = [$mobile];
            }
        }

        $mobile = array_map('trim', $mobile);

        if ($blacklist) {
            $mobile = $this->checkBlackList($mobile);
        }

        return $mobile;
    }

    private function checkBlackList($mobiles = [])
    {
        $blacklist = $this->get('db')
            ->table('addon_sms_blacklist')
            ->whereIn('mobile', $mobiles)
            ->pluck('mobile');

        return array_diff($mobiles, $blacklist);
    }

    public function getContent($filename)
    {
        $message = null;
        $path = resource_path('email') . '/en/';

        $filename = $path . $filename;

        if (file_exists($filename)) {
            $message = file_get_contents($filename);
        }

        return $message;
    }

    public function replace($vars = [], $message = [])
    {
        if (preg_match_all('~\{\$([^{}]+)\}~', $message, $matches) && count($matches[0]) > 0) {
            foreach ($matches[0] as $key => $match) {
                $message = str_replace($match, $this->find($matches[1][$key], $vars), $message);
            }
        }

        return $message;
    }

    public function find($string, $vars)
    {
        $str = explode('.', $string);
        foreach ($str as $key) {
            $vars = $vars[$key];
        }

        return $vars;
    }

    public function logSms($data = [], $status = true)
    {
        //log enable
        if (!$this->config('sms.log') || $data['log'] === false) {
            return true;
        }

        $values = [];
        $values['sender'] = $data['from'];
        $values['mobile'] = implode(', ', $data['to']);
        $values['message'] = $data['message'];
        $values['created_at'] = new Carbon;
        $values['reason'] = $data['reason'];
        $values['status'] = ($status) ? 1 : 0;

        $this->get('db')->table('addon_sms_logs')->insert($values);
    }
}
