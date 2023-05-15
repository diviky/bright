<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Support\Facades\App;

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
        $config = App::make('config')->get('bright');

        return [
            'databases' => $config['databases'],
            'tables' => $config['tables'],
            'connections' => $config['connections'],
            'async' => $config['async'],
            'sharding' => $config['sharding'],
            'timestamps' => $config['timestamps'],
            'db_events' => $config['db_events'],
            'db_cache' => $config['db_cache'],
        ];
    }
}
