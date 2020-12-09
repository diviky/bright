<?php

namespace Diviky\Bright\Database\Traits;

trait Async
{
    public function async($queue = null, $connection = null)
    {
        $config = $this->asyncConfig();
        if ($config['enable']) {
            $queue = $queue ?? $config['queue'];
            $connection = $connection ?? $config['connection'];

            $this->connection->async($connection, $queue);
        }

        return $this;
    }

    protected function asyncConfig()
    {
        $config = $this->connection->getConfig('bright.async');

        return isset($config) ? $config : [];
    }
}
