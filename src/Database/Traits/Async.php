<?php

namespace Diviky\Bright\Database\Traits;

trait Async
{
    public function async($queue = null, $connection = null): static
    {
        $config = $this->asyncConfig();
        if ($config['enable']) {
            $queue      = $queue ?? $config['queue'];
            $connection = $connection ?? $config['connection'];

            $this->connection->async($connection, $queue);
        }

        return $this;
    }

    protected function asyncConfig()
    {
        $bright = $this->getBrightConfig();

        return isset($bright['async']) ? $bright['async'] : [];
    }
}
