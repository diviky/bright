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
    /**
     * Store original view paths before modification
     */
    protected $originalViewPaths = [];

    /**
     * Store original view namespaces
     */
    protected $originalViewNamespaces = [];

    public function render()
    {
        $view = $this->setUpView()->getViewFrom($this);

        return view($view);
    }

    protected function setUpView()
    {
        // Store original view paths and namespaces before modifying
        // $this->storeOriginalViewConfiguration();

        // Added to avoid the cache views with same name in different components
        View::resetDefaultPaths();
        $finder = View::getFinder();
        $finder->flush();

        $paths = $this->getViewPathsFrom($this);
        $paths = array_filter($paths);
        $paths = array_reverse($paths);

        foreach ($paths as $path) {
            $finder->addLocation($path);
        }

        return $this;
    }

    /**
     * Store the original view configuration before modification
     */
    protected function storeOriginalViewConfiguration()
    {
        $finder = View::getFinder();
        $this->originalViewPaths = $finder->getPaths();
        $this->originalViewNamespaces = $finder->getHints();
    }

    /**
     * Restore the original view configuration
     */
    protected function restoreOriginalViewConfiguration()
    {
        if (!empty($this->originalViewPaths)) {
            $finder = View::getFinder();

            // Reset to original state
            View::resetDefaultPaths();
            $finder->setPaths([]);
            $finder->flush();

            // Restore original paths
            foreach ($this->originalViewPaths as $path) {
                $finder->addLocation($path);
            }

            // Restore original namespaces if they exist
            if (!empty($this->originalViewNamespaces)) {
                foreach ($this->originalViewNamespaces as $namespace => $hints) {
                    if (is_array($hints)) {
                        foreach ($hints as $hint) {
                            $finder->addNamespace($namespace, $hint);
                        }
                    }
                }
            }
        }

        // Clear stored paths after restoration
        $this->originalViewPaths = [];
        $this->originalViewNamespaces = [];
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

    public function dehydrate()
    {
        $this->dispatch('component.dehydrate');
    }

    public function rendered()
    {
        $this->dispatch('component.rendered');

        // Restore original view paths after rendering is complete
        $this->restoreOriginalViewConfiguration();
    }
}
