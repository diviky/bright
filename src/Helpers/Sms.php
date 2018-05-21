<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Helpers;

use Exception;
use Speedwork\Core\Helper;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Sms extends Helper
{
    public function sendSms($data = [])
    {
        $config = $this->config('sms');

        $tags = (is_array($data['tags'])) ? $data['tags'] : [];

        $tags['sitename'] = config('app.name');

        if ($data['template']) {
            $name = strtolower($data['template']);
            $name = str_replace(['.tpl', '.html', '.txt'], '', $name);
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
        $data['to']      = $this->formatMobileNumber($data['to']);

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

        $provider  = $config['provider'];
        $providers = $config['providers'];
        $config    = $providers[$provider];
        $config    = array_merge(['from' => $data['from']], $config);
        $driver    = $config['driver'];

        try {
            $sms  = $this->get('resolver')->helper($driver);
            $sent = $sms->send($data, $config);
        } catch (Exception $e) {
            $sent            = [];
            $sent['status']  = 'FAILED';
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
        $blacklist = $this->get('database')->find('#__addon_sms_blacklist', 'list', [
            'conditions' => ['mobile' => $mobiles],
            'fields'     => ['mobile'],
        ]);

        return array_diff($mobiles, $blacklist);
    }

    public function getContent($filename)
    {
        $message = null;
        $path    = path('email').'en'.DS;

        $filename = $path.$filename;

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

        $save            = [];
        $save['sender']  = $data['from'];
        $save['mobile']  = implode(', ', $data['to']);
        $save['message'] = $data['message'];
        $save['created'] = time();
        $save['reason']  = $data['reason'];
        $save['status']  = ($status) ? 1 : 0;

        $this->get('database')->save('#__addon_sms_logs', $save);
    }
}
