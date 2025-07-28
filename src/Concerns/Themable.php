<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Helpers\Device;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

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
     * @param  string  $action
     * @return string[]
     */
    public function setUpThemeFromAction($action, array $paths = []): array
    {
        $route = $this->getRouteFromAction($action);
        $component = \explode('.', $route, 2)[0];

        $matches = $this->getThemeFromRouteName($component);
        $template = $this->getMatchingTheme($matches);

        return $this->setUpTheme($template, $component, $paths);
    }

    /**
     * Setup theeme from action.
     *
     * @param  string  $route
     * @return string[]
     */
    public function setUpThemeFromRoute($route, ?string $component = null, array $paths = []): array
    {
        $matches = $this->getThemeFromRouteName($route);
        $template = $this->getMatchingTheme($matches);

        return $this->setUpTheme($template, $component, $paths);
    }

    /**
     * Setup the theme from route.
     *
     * @param  null|string  $template
     * @param  null|string  $component
     * @param  array  $paths
     */
    protected function setUpTheme($template, $component = null, $paths = []): array
    {
        if (is_null($template)) {
            $template = $this->getDefaultTheme();
        }

        $layout = null;
        if (preg_match('/^([^:]+):(?!:)(.+)$/', $template, $matches)) {
            $themeName = $matches[1];
            $layout = $matches[2];
        } else {
            // $layout = $template;
            $themeName = config('themes.active', 'bootstrap');
        }

        $themePaths = config('themes.paths', []);

        $themePath = isset($themePaths[$themeName]) ? $themePaths[$themeName] : null;
        $themePath = is_array($themePath) ? $themePath[0] : $themePath;

        if (is_null($themePath) && !empty($themeName)) {
            $themePath = config('themes.base_path', resource_path('views')) . '/' . $themeName;
        }

        $views = resource_path('views');
        $location = public_path($themeName);

        $theme = [
            'name' => $themeName,
            'layout' => $layout,
            'path' => $themePath,
            'location' => $location,
        ];

        View::share('theme', $theme);

        // Added to avoid the cache views with same name in different components
        View::resetDefaultPaths();
        $finder = View::getFinder();
        $finder->flush();

        $paths[] = $views;
        $paths[] = $component ? $views . '/components/' . $component : null;
        $paths[] = $themePath ? $themePath . '/views' : null;
        $paths[] = $themePath && $component ? $themePath . '/views/components/' . $component : $themePath;
        $paths = array_filter($paths);

        foreach ($paths as $path) {
            $finder->prependLocation(str_replace('//', '/', $path));
        }

        return $theme;
    }

    protected function getTheme(): array
    {
        $themes = config('themes');
        $device = $themes['device'];

        if (empty($device) || $device == 'auto') {
            $device = (new Device)->detect(env('HTTP_USER_AGENT'));
            config(['themes.device' => $device]);
        }

        $deviceType = !\is_array($device) ? $device : Arr::get($device, 'type');

        if (empty($themes[$deviceType])) {
            $deviceType = 'computer';
        }

        $theme = $themes['active'];
        $layouts = $themes['layouts'] ?? [];
        $theme = $layouts[$theme] ?? [];
        $default = $layouts['default'] ?? [];

        $values = array_merge($theme['default'], $theme[$deviceType] ?? []);

        return array_merge($default['default'] ?? [], $default[$deviceType] ?? [], $values);
    }

    /**
     * Identify the theme from route.
     */
    protected function getThemeFromRoute(Route $route): array
    {
        $action = $route->getActionName();

        $route_name = $this->getRouteFromAction($action);
        [$option, $view] = \explode('.', $route_name);

        return [
            $option . '.' . $view,
            $option . '.' . $view . '.*',
        ];
    }

    /**
     * Get the default theme.
     */
    protected function getDefaultTheme(): string
    {
        $matches = [
            'default',
        ];

        return $this->getMatchingTheme($matches) ?? 'bootstrap';
    }

    /**
     * Identify the theme from route.
     */
    protected function getThemeFromPrefix(Route $route): array
    {
        $action = $route->getActionName();

        $route_name = $this->getRouteFromAction($action);
        [$component, $method] = \explode('.', $route_name);

        $prefix = 'prefix:' . ltrim((string) $route->getPrefix(), '/');
        $prefix = str_replace('/', '.', $prefix);

        return [
            $prefix . $component . '.' . $method,
            $prefix . $component . '.' . $method . '.*',
        ];
    }

    /**
     * Identify the theme from route.
     */
    protected function getThemeFromName(Route $route): array
    {
        $route = $route->getName();

        if (\is_null($route)) {
            return [];
        }

        return [
            'name:' . $route,
            'name:' . $route . '.*',
        ];
    }

    protected function getThemeFromRouteName(?string $route = null): array
    {
        if (\is_null($route)) {
            return [];
        }

        return [
            'name:' . $route,
            'name:' . $route . '.*',
        ];
    }

    /**
     * Identify the theme from route.
     *
     * @param  mixed  $matches
     */
    protected function getMatchingTheme($matches): ?string
    {
        if (is_null($this->theme)) {
            $this->theme = $this->getTheme();
        }

        $template = null;
        foreach (array_keys($this->theme) as $theme) {
            foreach ($matches as $match) {
                if (Str::is($theme, $match)) {
                    $template = $this->theme[$theme];

                    break 2;
                }
            }
        }

        return is_array($template) ? $template[0] : $template;
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

        $matches = [];
        $matches = array_merge($matches, $this->getThemeFromName($route));
        $matches = array_merge($matches, $this->getThemeFromRoute($route));
        $matches = array_merge($matches, $this->getThemeFromPrefix($route));

        $template = $this->getMatchingTheme($matches);

        return $this->setUpTheme($template, $component, $paths);
    }
}
