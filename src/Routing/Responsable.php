<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Diviky\Bright\Attributes\Resource;
use Diviky\Bright\Attributes\View as AttributesView;
use Diviky\Bright\Attributes\ViewNamespace;
use Diviky\Bright\Attributes\ViewPaths;
use Diviky\Bright\Concerns\Themable;
use Illuminate\Contracts\Support\Responsable as BaseResponsable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class Responsable implements BaseResponsable
{
    use Themable;

    /**
     * @var mixed
     */
    protected $response;

    /**
     * @var string
     */
    protected $action;

    /**
     * Undocumented variable.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * @var string
     */
    protected $method;

    /**
     * @param  mixed  $response
     * @param  string  $action
     * @param  mixed  $controller
     * @param  string  $method
     */
    public function __construct($response, $action, $controller, $method)
    {
        $this->response = $response;
        $this->action = $action;
        $this->controller = $controller;
        $this->method = $method;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $response = $this->getResponse();

        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod($this->method);

        if ($request->post('fingerprint') || $request->hasHeader('X-Inertia') || $request->hasHeader('X-Livewire')) {
            return $response;
        }

        $format = $request->input('_request') ?: $request->input('format');

        if (!$format && $request->expectsJson()) {
            $attributes = $method->getAttributes(Resource::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $response = $instance->toResource($response);
            }

            return $response;
        }

        if (!\is_array($response) && $response instanceof View) {
            $this->setUpThemeFromRequest($request);

            return $response;
        }

        if (!\is_array($response)) {
            return $response;
        }

        if ($format == 'iframe') {
            $html = '<textarea>';
            $html .= \json_encode($response);
            $html .= '</textarea>';

            return $html;
        }

        $ajax = $request->ajax();

        if (!$ajax && isset($response['next'])) {
            return $this->getNextRedirect($response, 'next');
        }

        if ($ajax && isset($response['redirect'])) {
            if (
                \substr($response['redirect'], 0, 1) !== '/'
                && \substr($response['redirect'], 0, 4) !== 'http'
            ) {
                $redirect = $this->getNextRedirect($response, 'redirect');
                if (isset($redirect)) {
                    $response['redirect'] = $redirect->getTargetUrl();
                }
            }
        }

        if ($ajax && isset($response['route'])) {
            $redirect = $this->getNextRedirect($response, 'route');
            if (isset($redirect)) {
                $response['redirect'] = $redirect->getTargetUrl();
            }
        }

        if ($format == 'json' || (isset($response['_format']) && $response['_format'] == 'json')) {
            unset($response['_format']);

            $attributes = $method->getAttributes(Resource::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();

                $response = $instance->toResource($response);
            }

            return $response;
        }

        if ($request->pjax()) {
            $format = 'html';
        }

        $route = $this->getRouteFromAction($this->action);

        [$component, $view] = \explode('.', $route, 2);

        $layout = null;
        $attributes = $method->getAttributes(AttributesView::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getName();
            $layout = $instance->getLayout();
        }

        if ($view === 'none' || $view === 'json') {
            return $response;
        }

        $paths = $this->getViewPathsFrom($this->controller, $this->action);
        $attributes = $reflection->getAttributes(ViewPaths::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $paths = array_merge($paths, $instance->getPaths());
        }

        $attributes = $reflection->getAttributes(ViewNamespace::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getViewName($view);
        }

        if (empty($layout)) {
            $theme = $this->setUpThemeFromRequest($request, $component, $paths);

            if ($request->pjax() && Str::endsWith($theme['layout'], ':html')) {
                $layout = Str::replaceLast('.', '.html.', Str::replaceLast(':html', '', $theme['layout']));
            } elseif ($format == 'html') {
                $layout = 'layouts.html';
            } else {
                $layout = Str::replaceLast(':html', '', $theme['layout']);
            }
        }

        $view = $this->getView($view, $response, $layout);

        $pjax = $request->pjax() ? true : $request->input('pjax');
        $fragment = $pjax ? false : $request->ajax();

        if ($fragment) {
            $container = $request->header('X-Pjax-Container', 'content');

            return [
                'fragments' => [
                    $container => $view->fragment($container),
                    //'indicator' => $view->fragment('indicator'),
                ],
            ];
        }

        return $view->render();
    }

    /**
     * Get the value of data.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
