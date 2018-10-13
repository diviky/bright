<?php

namespace Karla\Routing;

use Exception;
use Illuminate\Contracts\Container\Container;
use Karla\Traits\CapsuleManager;

class Resolver
{
    use CapsuleManager;

    public function __construct(Container $app)
    {
        $this->setContainer($app);
    }

    /**
     * Convert the option to CamelCase.
     *
     * @param string $option
     *
     * @return string Converted string
     */
    protected function sanitize($option)
    {
        return ucfirst(strtolower($option));
    }

    /**
     * Get the view paths and namespace for option.
     *
     * @param string $option
     * @param string $type   Type of option
     *
     * @return array View paths and namespace
     */
    protected function getPath($option, $type = 'controllers')
    {
        $folder = ucfirst($type);
        $option = $this->sanitize($option);

        $default = [
            'namespace' => $this->app->getNameSpace().'Http\\'.$folder.'\\'.$option.'\\',
        ];

        return $default;
    }

    /**
     * Get the namespace for option.
     *
     * @param string $option
     * @param string $type
     *
     * @return string Namespace
     */
    protected function getNameSpace($option, $type = 'controllers')
    {
        $path      = $this->getPath($option, $type);
        $namespace = (is_array($path)) ? $path['namespace'] : $path;

        return rtrim($namespace, '\\').'\\';
    }

    /**
     * Load helper class.
     *
     * @param string $name Helper name
     *
     * @throws Exception
     *
     * @return object
     */
    public function getHelper($name, $namespace = null, $singletone = true)
    {
        if (!is_string($name) && $name instanceof Closure) {
            return $name($this->getContainer());
        }

        $signature = 'helper.'.strtolower($name);

        if ($singletone && $this->has($signature)) {
            return $this->get($signature);
        }

        $helper = $name;
        $group  = null;
        $option = null;

        if (false !== strpos($name, ':')) {
            list($name, $group) = explode(':', $name);
        }

        if (false !== strpos($name, '.')) {
            list($helper, $option) = explode('.', $name);
        }

        $paths       = [];
        $helperClass = 'Helpers\\'.(($group) ? $group.'\\' : '').ucfirst($helper);

        if ($namespace) {
            $paths[] = [
                'class' => str_replace('/', '\\', $namespace).'\\'.$helperClass,
            ];
        } else {
            if ($option) {
                $option    = $this->sanitize($option);
                $namespace = $this->getNameSpace($option);

                $paths[] = [
                    'class' => $namespace.$helperClass,
                ];
            } else {
                $paths[] = [
                    'class' => $this->app->getNamespace().$helperClass,
                ];

                $paths[] = [
                    'class' => 'Karla\\'.$helperClass,
                ];
            }
        }

        foreach ($paths as $path) {
            $exists = false;
            if (class_exists($path['class'])) {
                $exists = true;
            }

            if ($exists) {
                $helperClass = $path['class'];
                $instance    = $this->app->make($helperClass);

                if (method_exists($instance, 'setContainer')) {
                    $instance->setContainer($this->getContainer());
                }

                $beforeRun = 'beforeRun';
                if (method_exists($instance, $beforeRun)) {
                    $instance->$beforeRun();
                }

                $this->set($signature, $instance);

                return $instance;
            }
        }

        throw new Exception($helper.' helper not found');
    }
}
