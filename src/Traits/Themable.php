<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Karla\Util\Device;

trait Themable
{
    protected function setUpTheme($route, $component = null, $paths = [])
    {
        $template             = $this->identify($route);
        list($theme, $layout) = explode('.', $template.'.');

        $themePath = resource_path('themes/'.$theme);
        $location  = public_path($theme);

        $theme = [
            'name'     => $theme,
            'layout'   => $layout,
            'path'     => $themePath,
            'location' => $location,
        ];

        View::share('theme', $theme);

        View::prependLocation($themePath);
        View::prependLocation($themePath.'/views/'.$component);

        if (!empty($paths)) {
            $paths = !is_array($paths) ? [$paths] : $paths;
            foreach ($paths as $path) {
                View::prependLocation($path);
            }
        }

        return $theme;
    }

    protected function identify($route)
    {
        $themes = config('theme');
        $device = $themes['device'];

        if (empty($device)) {
            $device = (new Device())->detect();
            config(['theme.device' => $device]);
        }

        $deviceType = !is_array($device) ? $device : $device['type'];

        $user = Auth::user();
        if ($user) {
            $themes = $this->userLevelTheme($user, $themes);
        }

        if (empty($themes[$deviceType])) {
            $deviceType = 'computer';
        }
        $theme = $themes[$deviceType];

        list($option, $view) = explode('.', $route);

        $matches = [
            $option.'.'.$view,
            $option.'.*',
            'default',
        ];

        $template = null;
        foreach ($matches as $match) {
            if (isset($theme[$match])) {
                $template = $theme[$match];
                break;
            }
        }

        if (empty($template)) {
            $theme = $themes['default'];
            if (is_array($theme)) {
                foreach ($matches as $match) {
                    if (isset($theme[$match])) {
                        $template = $theme[$match];
                        break;
                    }
                }
            } else {
                $template = $theme;
            }
        }

        return $template;
    }

    protected function userLevelTheme($user, $themes = [])
    {
        // User level theme support
        $meta = $user->options;

        if ($meta && !is_array($meta)) {
            $meta = json_decode($meta, true);
            if (is_array($meta) && is_array($meta['theme'])) {
                $themes = array_replace_recursive($themes, $meta['theme']);
            }
        }

        return $themes;
    }
}
