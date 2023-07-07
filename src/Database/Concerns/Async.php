<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

trait Async
{
    /**
     * Async config.
     *
     * @var null|array
     */
    protected $async;

    /**
     * Run the query in async mode.
     *
     * @param null|string $queue
     * @param null|string $connection
     * @param null|string $name
     *
     * @return static
     */
    public function async($name = null, $queue = null, $connection = null)
    {
        $config = $this->asyncConfig();

        if (!empty($config['enable']) || !empty($config['all'])) {
            $queue = $queue ?? $config['queue'];
            $connection = $connection ?? $config['connection'];
            $name = $name ?? $this->getTableBaseName();

            $this->connection->async($connection, $queue, $name);
            $this->async = [$connection, $queue, $name];
        }

        return $this;
    }

    public function getAsync(): ?array
    {
        return $this->async;
    }

    public function sync(): self
    {
        $this->connection->sync();
        $this->async = null;

        return $this;
    }

    /**
     * Get the async config.
     *
     * @return array
     */
    protected function asyncConfig()
    {
        $bright = $this->getConfig();

        return isset($bright['async']) ? $bright['async'] : [];
    }
}
