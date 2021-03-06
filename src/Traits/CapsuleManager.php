<?php

namespace Diviky\Bright\Traits;

use Illuminate\Container\Container;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
trait CapsuleManager
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * magic method to get property.
     *
     * @param string $key value to get
     *
     * @return bool
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * magic method to get property.
     *
     * @param string $key   value to get
     * @param mixed  $value
     *
     * @return bool
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Make this capsule instance available globally.
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        if ($this->app) {
            return $this->app;
        }

        return Container::getInstance();
    }

    /**
     * Set the IoC container instance.
     */
    public function setContainer(Container $app)
    {
        $this->app = $app;
    }

    /**
     * get the key from stored data value.
     *
     * @param string $key The name of the variable to access
     *
     * @return mixed returns your stored value
     */
    public function get($key)
    {
        return $this->getContainer()->get($key);
    }

    /**
     * get the key from stored data value.
     *
     * @param string $key The name of the variable to access
     *
     * @return mixed returns your stored value
     */
    public function has($key)
    {
        return $this->getContainer()->has($key);
    }

    /**
     * store key value pair in registry.
     *
     * @param string $key   name of the variable
     * @param mixed  $value value to store in registry
     */
    public function set($key, $value)
    {
        $this->getContainer()->instance($key, $value);

        return $this;
    }

    public function take($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return $default;
    }

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed        $default
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function config($key = null, $default = null)
    {
        return config($key, $default);
    }
}
