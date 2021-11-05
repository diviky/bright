<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Helpers\Device;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

trait Themable
{
    use Responsable;

    /**
     * Current theme settings.
     *
     * @var null|array
     */
    protected $theme;

    /**
     * Setup theeme from action.
     *
     * @param string $action
     *
     * @return string[]
     */
    public function setUpThemeFromAction($action, ?string $component = null, array $paths = []): array
    {
        $route = $this->getRouteFromAction($action);
        $template = $this->getThemeFromRoute($route);

        return $this->setUpTheme($template, $component, $paths);
    }

    /**
     * Setup theeme from action.
     *
     * @param string $route
     *
     * @return string[]
     */
    public function setUpThemeFromRoute($route, ?string $component = null, array $paths = []): array
    {
        $template = $this->getThemeFromRoute($route);

        return $this->setUpTheme($template, $component, $paths);
    }

    /**
     * Setup the theme from route.
     *
     * @param null|string $template
     * @param null|string $component
     * @param array       $paths
     */
    protected function setUpTheme($template, $component = null, $paths = []): array
    {
        if (is_null($template)) {
            $template = $this->getDefaultTheme();
        }

        list($theme, $layout) = \explode('.', $template . '.');

        $themePath = resource_path('themes/' . $theme);
        $views = resource_path('views');
        $location = public_path($theme);

        $theme = [
            'name' => $theme,
            'layout' => $layout,
            'path' => $themePath,
            'location' => $location,
        ];

        View::share('theme', $theme);

        // Added to avoid the cache views with same name in different components
        View::resetDefaultPaths();
        $finder = View::getFinder();
        $finder->flush();

        $paths[] = $views . '/' . $component;
        $paths[] = $themePath;
        $paths[] = $themePath . '/views/' . $component;

        foreach ($paths as $path) {
            $finder->prependLocation($path);
        }

        return $theme;
    }

    protected function getTheme(): array
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

        if (!is_array($theme)) {
            $theme = [];
        }

        return array_merge($themes['default'], $theme);
    }

    /**
     * Identify the theme from route.
     *
     * @param string $route
     */
    protected function getThemeFromRoute($route): ?string
    {
        list($option, $view) = \explode('.', $route);

        $matches = [
            $option . '.' . $view,
            $option . '.*',
        ];

        return $this->getMatchingTheme($matches);
    }

    /**
     * Get the default theme.
     */
    protected function getDefaultTheme(): string
    {
        $matches = [
            'default',
        ];

        return $this->getMatchingTheme($matches) ?? 'tabler';
    }

    /**
     * Identify the theme from route.
     *
     * @param \Illuminate\Routing\Route $route
     * @param string                    $component
     * @param null|string               $method
     */
    protected function getThemeFromPrefix($route, $component, $method = null): ?string
    {
        $prefix = 'prefix:' . $route->getPrefix();
        $prefix = str_replace('/', '.', $prefix);

        $matches = [
            $prefix . '.' . $component . ':' . $method,
            $prefix . '.' . $component . ':*',
            $prefix . '.*',
        ];

        return $this->getMatchingTheme($matches);
    }

    /**
     * Identify the theme from route.
     *
     * @param \Illuminate\Routing\Route $route
     */
    protected function getThemeFromName($route): ?string
    {
        $route = $route->getName();

        if (\is_null($route)) {
            return null;
        }

        $matches = [
            'name:' . $route,
            'name:' . $route . '.*',
        ];

        return $this->getMatchingTheme($matches);
    }

    /**
     * Identify the theme from route.
     *
     * @param string $route
     * @param mixed  $matches
     */
    protected function getMatchingTheme($matches): ?string
    {
        if (is_null($this->theme)) {
            $this->theme = $this->getTheme();
        }

        $template = null;
        foreach ($matches as $match) {
            if (isset($this->theme[$match])) {
                $template = $this->theme[$match];

                break;
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
    protected function setUpThemeFromRequest(Request $request, ?string $component = null, array $paths = []): array
    {
        $route = $request->route();
        if (!$route instanceof Route) {
            return [];
        }

        $action = $route->getActionName();

        $route_name = $this->getRouteFromAction($action);

        $template = $this->getThemeFromRoute($route_name);

        if (is_null($template)) {
            list($option, $view) = \explode('.', $route_name);

            $template = $this->getThemeFromPrefix($route, $option, $view);
        }

        if (is_null($template)) {
            $template = $this->getThemeFromName($route);
        }

        return $this->setUpTheme($template, $component, $paths);
    }
}
