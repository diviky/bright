<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Async
{
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
        $this->query->setEloquent($this)->async($name, $queue, $connection);

        return $this;
    }
}
