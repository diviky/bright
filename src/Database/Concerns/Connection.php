<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Events\QueryQueued;
use Illuminate\Database\QueryException;

trait Connection
{
    /**
     * Number of attempts to retry.
     */
    public int $attempts_count = 3;

    /**
     * Async config.
     *
     * @var null|array
     */
    protected $async;

    /**
     * Async config.
     *
     * @var array
     */
    protected $query_events = [];

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array  $bindings
     * @param mixed  $useReadPdo
     *
     * @return array|bool|int
     */
    public function statement($query, $bindings = [])
    {
        if (preg_match_all('/#__([^\s]+)/', $query, $matches)) {
            foreach ($matches[1] as $table) {
                $query = \str_replace('#__' . $table . ' ', $this->getDefaultQueryGrammar()->wrapTable($table) . ' ', $query);
            }
        }

        $prefix = $this->getTablePrefix();
        $query = \str_replace('#__', $prefix, $query);

        if ($this->hasEvents()) {
            $this->fireEvents($query, $bindings);
        }

        if ($this->shouldQueue()) {
            $this->toQueue($query, $bindings);

            return true;
        }

        $type = \trim(\strtolower(\explode(' ', $query)[0]));

        switch ($type) {
            case 'delete':
                return parent::affectingStatement($query, $bindings);

                break;
            case 'update':
                return parent::affectingStatement($query, $bindings);

                break;
            case 'insert':
                return parent::statement($query, $bindings);

                break;
            case 'select':
                if (\preg_match('/outfile\s/i', $query)) {
                    return parent::statement($query, $bindings);
                }

                return $this->select($query, $bindings);

                break;
            case 'load':
                return $this->unprepared($query);

                break;
        }

        return parent::statement($query, $bindings);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        if ($this->hasEvents()) {
            $this->fireEvents($query, $bindings);
        }

        if ($this->shouldQueue()) {
            $this->toQueue($query, $bindings);

            return 1;
        }

        return parent::affectingStatement($query, $bindings);
    }

    /**
     * Run the query in async mode.
     *
     * @param null|string $connection
     * @param string      $queue
     * @param null|string $name
     */
    public function async($connection = null, $queue = null, $name = null): self
    {
        $this->async = [$connection, $queue, $name];

        return $this;
    }

    public function sync(): self
    {
        $this->async = null;

        return $this;
    }

    public function toQueue(string $query, array $bindings): self
    {
        $async = $this->async;
        $this->async = null;
        $this->event(new QueryQueued($query, $bindings, $async));

        unset($async);

        return $this;
    }

    /**
     * Run the query events mode.
     *
     * @param array|string $events
     */
    public function events($events = null): self
    {
        if (!is_array($events)) {
            $events = [$events];
        }

        $this->query_events = array_merge($this->query_events, $events);

        return $this;
    }

    /**
     * Run a SQL statement.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, \Closure $callback)
    {
        $attempts_count = $this->attempts_count;

        for ($attempt = 1; $attempt <= $attempts_count; ++$attempt) {
            try {
                return parent::runQueryCallback($query, $bindings, $callback);
            } catch (QueryException $e) {
                if ($attempt >= $attempts_count) {
                    throw $e;
                }

                if (!$this->causedByConcurrencyError($e)) {
                    throw $e;
                }
            }
        }
    }

    protected function shouldQueue(): bool
    {
        if (\is_array($this->async)) {
            return true;
        }

        $config = $this->asyncConfig();

        if (!empty($config['all'])) {
            $this->async($config['connection'], $config['queue']);

            return true;
        }

        return false;
    }

    /**
     * Get the async config.
     *
     * @return array
     */
    protected function asyncConfig()
    {
        $bright = $this->config['bright'] ?? [];

        return isset($bright['async']) ? $bright['async'] : [];
    }

    protected function hasEvents(): bool
    {
        if (!empty($this->query_events)) {
            return true;
        }

        return false;
    }

    /**
     * Fire the query events.
     *
     * @param string $query
     * @param array  $bindings
     */
    protected function fireEvents($query, $bindings): self
    {
        $events = $this->query_events;
        $this->query_events = [];

        foreach ($events as $event) {
            if ($event instanceof \Closure) {
                $this->event($event($query, $bindings));
            } else {
                $this->event(new $event($query, $bindings));
            }
        }

        unset($events);

        return $this;
    }
}
