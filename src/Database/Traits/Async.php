<?php

namespace Diviky\Bright\Database\Traits;

trait Async
{
    protected $async_connection = false;
    protected $async_queue      = 'queries';

    public function async($connection = 'default', $queue = 'queries')
    {
        $this->async_connection = $connection;
        $this->async_queue      = $queue;

        return $this;
    }

    protected function isAsync()
    {
        if (!$this->async_connection) {
            return false;
        }

        if (!$this->asyncEnabled()) {
            return false;
        }

        return true;
    }

    protected function doAsync()
    {
        $this->connection->async(null);

        if ($this->isAsync()) {
            $this->connection->async($this->async_connection, $this->async_queue);
        }

        return true;
    }

    protected function asyncEnabled()
    {
        return $this->connection->getConfig('async');
    }
}
