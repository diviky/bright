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
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

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

    public function __construct(
        mixed $response,
        string $action,
        mixed $controller,
        string $method
    ) {
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
    #[\Override]
    public function toResponse($request): mixed
    {
        $response = $this->getResponse();
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod($this->method);

        if ($this->shouldReturnDirectResponse($request)) {
            return $response;
        }

        $format = $this->getRequestFormat($request);

        if ($this->shouldReturnJsonResponse($request, $format)) {
            return $this->handleJsonResponse($response, $method);
        }

        if ($this->isViewResponse($response)) {
            $this->setUpThemeFromRequest($request);

            return $response;
        }

        if ($format == 'json' || (isset($response['_format']) && $response['_format'] == 'json')) {
            unset($response['_format']);

            return $this->handleJsonResponse($response, $method);
        }

        if (!$this->isArrayOrResponse($response)) {
            return $response;
        }

        if ($format === 'iframe') {
            return $this->handleIframeResponse($response);
        }

        return $this->handleViewResponse($request, $response, $method, $reflection);
    }

    protected function shouldReturnDirectResponse(Request $request): bool
    {
        return $request->post('fingerprint')
            || $request->hasHeader('X-Inertia')
            || $request->hasHeader('X-Livewire');
    }

    protected function getRequestFormat(Request $request): ?string
    {
        return $request->input('_request') ?: $request->input('format');
    }

    protected function shouldReturnJsonResponse(Request $request, ?string $format): bool
    {
        return $format === 'json' && $request->expectsJson();
    }

    protected function handleJsonResponse(mixed $response, ReflectionMethod $method): mixed
    {
        $attributes = $method->getAttributes(Resource::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $response = $instance->toResource($response);
        }

        return $response;
    }

    protected function isViewResponse(mixed $response): bool
    {
        return !is_array($response) && $response instanceof View;
    }

    protected function isArrayOrResponse(mixed $response): bool
    {
        return is_array($response) || $response instanceof Response;
    }

    protected function handleIframeResponse(mixed $response): string
    {
        return '<textarea>' . json_encode($response) . '</textarea>';
    }

    protected function handleViewResponse(
        Request $request,
        mixed $response,
        ReflectionMethod $method,
        ReflectionClass $reflection
    ): mixed {
        if (is_array($response)) {
            $response = $this->handleArrayResponse($request, $response);
        }

        $route = $this->getRouteFromAction($this->action);
        [$component, $view] = explode('.', $route, 2);

        $viewConfig = $this->getViewConfiguration($method, $view);
        if ($viewConfig['view'] === 'none' || $viewConfig['view'] === 'json') {
            return $response;
        }

        $paths = $this->getViewPaths($reflection);
        $view = $this->applyViewNamespace($reflection, $viewConfig['view']);
        $theme = $this->setUpThemeFromRequest($request, $component, $paths);

        $layout = $this->determineLayout($request, $theme, $viewConfig['layout']);

        $pjax = $request->pjax() ? true : boolval($request->input('pjax'));
        $fragment = $pjax ? false : $request->ajax();

        if ($fragment) {
            return $this->handleAjaxResponse($request, $response, $viewConfig['view'], $layout);
        }

        return $this->renderView($response, $viewConfig['view'], $layout);
    }

    protected function handleArrayResponse(Request $request, array $response): array
    {
        if (!$request->ajax() && isset($response['next'])) {
            $redirect = $this->getNextRedirect($response, 'next');

            return $redirect instanceof Response ? ['redirect' => $redirect->getTargetUrl()] : $response;
        }

        if ($request->ajax()) {
            if (isset($response['redirect'])) {
                $response = $this->handleRedirectResponse($response);
            }
            if (isset($response['route'])) {
                $response = $this->handleRouteResponse($response);
            }
        }

        return $response;
    }

    protected function handleRedirectResponse(array $response): array
    {
        if (
            substr($response['redirect'], 0, 1) !== '/'
            && substr($response['redirect'], 0, 4) !== 'http'
        ) {
            $redirect = $this->getNextRedirect($response, 'redirect');
            if ($redirect instanceof Response) {
                $response['redirect'] = $redirect->getTargetUrl();
            }
        }

        return $response;
    }

    protected function handleRouteResponse(array $response): array
    {
        $redirect = $this->getNextRedirect($response, 'route');
        if ($redirect instanceof Response) {
            $response['redirect'] = $redirect->getTargetUrl();
        }

        return $response;
    }

    protected function getViewConfiguration(ReflectionMethod $method, string $view): array
    {
        $layout = null;
        $attributes = $method->getAttributes(AttributesView::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getName();
            $layout = $instance->getLayout();
        }

        return ['view' => $view, 'layout' => $layout];
    }

    protected function getViewPaths(ReflectionClass $reflection): array
    {
        $paths = $this->getViewPathsFrom($this->controller, $this->action);

        $attributes = $reflection->getAttributes(ViewPaths::class);
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $paths = array_merge($paths, $instance->getPaths());
        }

        return $paths;
    }

    protected function applyViewNamespace(ReflectionClass $reflection, string $view): string
    {
        $attributes = $reflection->getAttributes(ViewNamespace::class);
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getViewName($view);
        }

        return $view;
    }

    protected function determineLayout(Request $request, array $theme, ?string $layout): string
    {
        if (!empty($layout)) {
            return $layout;
        }

        $format = $request->input('format');
        $pjax = $request->pjax() ?: $request->input('pjax');
        $fragment = !$pjax && $request->ajax();

        if ($request->pjax() && Str::endsWith($theme['layout'], ':html')) {
            return Str::replaceLast('.', '.html.', Str::replaceLast(':html', '', $theme['layout']));
        }

        if ($request->pjax()) {
            $format = 'html';
        }

        if ($format === 'html') {
            return $fragment ? 'layouts.fragment' : 'layouts.html';
        }

        return Str::replaceLast(':html', '', $theme['layout']);
    }

    protected function handleAjaxResponse(Request $request, mixed $response, string $view, string $layout): array
    {
        $container = $request->header('X-Pjax-Container', 'content');

        if ($response instanceof Response) {
            $content = $response->getContent();
            $response = [];
        } else {
            $content = $this->getView($view, $response)->fragment($container);
        }

        $view = $this->getViewLayout($content, $response, $layout);

        return [
            'fragments' => [
                $container => $view->render(),
            ],
        ];
    }

    protected function renderView(mixed $response, string $view, string $layout): string
    {
        if ($response instanceof Response) {
            $content = $response->getContent();
            $response = [];
        } else {
            $content = $this->getViewContent($view, $response);
        }

        $view = $this->getViewLayout($content, $response, $layout);

        return $view->render();
    }

    /**
     * Get the value of data.
     */
    public function getResponse(): mixed
    {
        return $this->response;
    }
}
