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
    public function setUpThemeFromAction($action, ?string $component = null, array $paths = []): array
    {
        $route = $this->getRouteFromAction($action);
        $matches = $this->getThemeFromRoute($route);
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
        $matches = $this->getThemeFromRoute($route);
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

        if (Str::contains($template, '|')) {
            [$themeName, $layout] = \explode('|', $template);
        } else {
            $layout = $template;
            $themeName = config('theme.active', 'boostrap');
        }

        $themePaths = config('theme.paths', []);

        $themePath = isset($themePaths[$themeName]) ? $themePaths[$themeName] : null;
        $themePath = is_array($themePath) ? $themePath[0] : $themePath;

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

        $paths[] = $views . '/' . $component;
        $paths[] = $themePath;
        $paths[] = $themePath . '/views/' . $component;

        $paths = array_filter($paths);

        foreach ($paths as $path) {
            $finder->prependLocation($path);
        }

        return $theme;
    }

    protected function getTheme(): array
    {
        $themes = config('theme');
        $device = $themes['device'];

        if (empty($device) || $device == 'auto') {
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

        $layout = $themes['layout'] ?? 'default';
        $layouts = $themes['layouts'] ?? [];
        $theme = $layouts[$layout] ?? [];

        return array_merge($themes['default'], $themes[$deviceType] ?? [], $theme['default'], $theme[$deviceType] ?? []);
    }

    /**
     * Identify the theme from route.
     *
     * @param  string  $route
     */
    protected function getThemeFromRoute($route): array
    {
        [$option, $view] = \explode('.', $route);

        return [
            $option . '.' . $view,
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
     *
     * @param  Route  $route
     * @param  string  $component
     * @param  null|string  $method
     */
    protected function getThemeFromPrefix($route, $component, $method = null): array
    {
        $prefix = 'prefix:' . ltrim((string) $route->getPrefix(), '/');
        $prefix = str_replace('/', '.', $prefix);

        return [
            $prefix . '.' . $component . '.' . $method,
        ];
    }

    /**
     * Identify the theme from route.
     *
     * @param  Route  $route
     */
    protected function getThemeFromName($route): array
    {
        $route = $route->getName();

        if (\is_null($route)) {
            return [];
        }

        return [
            'name:' . $route,
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
     * Check user level theme avaliable.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|Model  $user
     * @param  array  $themes
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
        [$option, $view] = \explode('.', $route_name);

        $matches = [];
        $matches = array_merge($matches, $this->getThemeFromRoute($route_name));
        $matches = array_merge($matches, $this->getThemeFromPrefix($route, $option, $view));
        $matches = array_merge($matches, $this->getThemeFromName($route));

        $template = $this->getMatchingTheme($matches);

        return $this->setUpTheme($template, $component, $paths);
    }
}
