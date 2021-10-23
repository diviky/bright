<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

trait Config
{
    /**
     * Set the builder config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Set the builder config.
     */
    public function setConfig(array $config = []): self
    {
        $this->config = \array_merge($this->config, $config);

        return $this;
    }

    /**
     * Get the configuration.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getConfig()
    {
        return config('bright');
    }
}
