<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Attributes\View as AttributesView;
use Diviky\Bright\Attributes\ViewNamespace;
use Diviky\Bright\Attributes\ViewPaths;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

trait Renderer
{
    public function render()
    {
        $view = $this->setUpView()->getViewFrom($this);

        return view($view);
    }

    protected function setUpView()
    {
        // Added to avoid the cache views with same name in different components
        View::resetDefaultPaths();
        $finder = View::getFinder();
        $finder->flush();

        $paths = $this->getViewPathsFrom($this);
        $paths = array_filter($paths);

        foreach ($paths as $path) {
            $finder->prependLocation($path);
        }

        return $this;
    }

    /**
     * Get the view name from controller.
     *
     * @param  self  $controller
     * @return array
     */
    protected function getViewFrom($controller)
    {
        $view = Str::kebab(class_basename($this));

        $reflection = new \ReflectionClass($controller);
        $attributes = $reflection->getAttributes(AttributesView::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getName();
        }

        $attributes = $reflection->getAttributes(ViewNamespace::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $view = $instance->getViewName($view);
        }

        return $view;
    }

    /**
     * Get the view locations from controller.
     *
     * @param  self  $controller
     * @return array
     */
    protected function getViewPathsFrom($controller)
    {
        $paths = [];
        if (\method_exists($controller, 'loadViewsFrom')) {
            $paths = $controller->getViewsFrom();
            $paths = !\is_array($paths) ? [$paths] : $paths;

            foreach ($paths as $key => $path) {
                $paths[$key] = $path . '/views/';
            }
        }

        $reflection = new \ReflectionClass($controller);

        $attributes = $reflection->getAttributes(ViewPaths::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $paths = array_merge($paths, $instance->getPaths());
        }

        return $paths;
    }
}
