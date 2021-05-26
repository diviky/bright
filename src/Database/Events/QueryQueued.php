<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Events;

class QueryQueued
{
    /**
     * The SQL query that was executed.
     *
     * @var string
     */
    public $sql;

    /**
     * The array of query bindings.
     *
     * @var array
     */
    public $bindings;

    /**
     * async configuration.
     *
     * @var array
     */
    public $async;

    /**
     * Create a new event instance.
     *
     * @param string $sql
     * @param array  $bindings
     * @param array  $async
     */
    public function __construct($sql, $bindings, $async)
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->async = $async;
    }
}
