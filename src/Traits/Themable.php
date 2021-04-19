<?php

namespace Diviky\Bright\Traits;

use Diviky\Bright\Util\Device;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

trait Themable
{
    use Responsable;

    public function setUpThemeFromAction($action)
    {
        $route = $this->getRoute($action);

        return $this->setUpTheme($route);
    }

    protected function setUpTheme($route, $component = null, $paths = [])
    {
        $template             = $this->identify($route);
        list($theme, $layout) = \explode('.', $template . '.');

        $themePath = resource_path('themes/' . $theme);
        $views     = resource_path('views');
        $location  = public_path($theme);

        $theme = [
            'name'     => $theme,
            'layout'   => $layout,
            'path'     => $themePath,
            'location' => $location,
        ];

        View::share('theme', $theme);

        $paths   = !\is_array($paths) ? [$paths] : $paths;
        $paths[] = $views . '/' . $component;
        $paths[] = $themePath;
        $paths[] = $themePath . '/views/' . $component;

        foreach ($paths as $path) {
            View::prependLocation($path);
        }

        return $theme;
    }

    protected function identify($route)
    {
        $themes = config('theme');
        $device = $themes['device'];

        if (empty($device) || 'auto' == $device) {
            $device = (new Device())->detect();
            config(['theme.device' => $device]);
        }

        $deviceType = !\is_array($device) ? $device : Arr::get($device, 'type');

        $user = Auth::user();
        if ($user && $user->options) {
            $themes = $this->userLevelTheme($user, $themes);
        }

        if (empty($themes[$deviceType])) {
            $deviceType = 'computer';
        }

        $theme = $themes[$deviceType];

        list($option, $view) = \explode('.', $route);

        $matches = [
            $option . '.' . $view,
            $option . '.*',
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
            $theme = $themes['default'] ?? null;
            if (\is_array($theme)) {
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

        if ($meta && !\is_array($meta)) {
            $meta = \json_decode($meta, true);
            if (\is_array($meta) && isset($meta['theme']) && \is_array($meta['theme'])) {
                $themes = \array_replace_recursive($themes, $meta['theme']);
            }
        }

        return $themes;
    }

    protected function setUpThemeFromRequest($request)
    {
        $route  = $request->route();
        $action = $route->getActionName();
        $this->setUpThemeFromAction($action);
    }
}
