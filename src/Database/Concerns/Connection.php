<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Closure;
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
     */
    public function async($connection = null, $queue = null): self
    {
        $this->async = [$connection, $queue];

        return $this;
    }

    public function toQueue(string $query, array $bindings): void
    {
        $async = $this->async;
        $this->async = null;
        $this->event(new QueryQueued($query, $bindings, $async));
    }

    /**
     * Run a SQL statement.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @throws \Illuminate\Database\QueryException
     *
     * @return mixed
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
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
}
