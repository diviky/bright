<?php

declare(strict_types=1);

namespace Diviky\Bright\Traits;

use Diviky\Bright\Helpers\Device;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

trait Themable
{
    use Responsable;

    /**
     * Setup theeme from action.
     *
     * @param string $action
     *
     * @return string[]
     */
    public function setUpThemeFromAction($action): array
    {
        $route = $this->getRoute($action);

        return $this->setUpTheme($route);
    }

    /**
     * Setup the theme from route.
     *
     * @param string      $route
     * @param null|string $component
     * @param array       $paths
     */
    protected function setUpTheme($route, $component = null, $paths = []): array
    {
        $template             = $this->identifyTheme($route);
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

        $paths[] = $views . '/' . $component;
        $paths[] = $themePath;
        $paths[] = $themePath . '/views/' . $component;

        foreach ($paths as $path) {
            View::prependLocation($path);
        }

        return $theme;
    }

    /**
     * Identify the theme from route.
     *
     * @param string $route
     */
    protected function identifyTheme($route): string
    {
        $themes = config('theme');
        $device = $themes['device'];

        if (empty($device) || 'auto' == $device) {
            $device = (new Device())->detect(env('HTTP_USER_AGENT'));
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

    /**
     * Check user level theme avaliable.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|Model $user
     * @param array                                            $themes
     *
     * @return array
     */
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

    /**
     * Setup theme from request.
     */
    protected function setUpThemeFromRequest(Request $request): void
    {
        $route  = $request->route();
        if (isset($route)) {
            $action = $route->getActionName();
            if (isset($action)) {
                $this->setUpThemeFromAction($action);
            }
        }
    }
}
