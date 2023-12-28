<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Container\Container;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
trait CapsuleManager
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * magic method to get property.
     *
     * @param  string  $key value to get
     * @return bool
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * magic method to get property.
     *
     * @param  string  $key   value to get
     * @param  mixed  $value
     * @return self
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);

        return $this;
    }

    /**
     * Make this capsule instance available globally.
     */
    public function setAsGlobal(): void
    {
        static::$instance = $this;
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return Container::getInstance();
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     */
    public function setContainer($app): self
    {
        $this->app = $app;

        return $this;
    }

    /**
     * get the key from stored data value.
     *
     * @param  string  $key The name of the variable to access
     * @return mixed returns your stored value
     */
    public function get($key)
    {
        return $this->getContainer()->get($key);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        return $this->getContainer()->has($key);
    }

    /**
     * store key value pair in registry.
     *
     * @param  string  $key   name of the variable
     * @param  mixed  $value value to store in registry
     */
    public function set($key, $value): self
    {
        $this->getContainer()->instance($key, $value);

        return $this;
    }

    /**
     * Get the value from stored data set, return default on null.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
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
     * @param  array|string  $key
     * @param  mixed  $default
     * @return \Illuminate\Config\Repository|mixed
     */
    public function config($key = null, $default = null)
    {
        return config($key, $default);
    }
}
