<?php

namespace Diviky\Bright\Database;

use Closure;
use Diviky\Bright\Database\Events\QueryQueued;
use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Illuminate\Database\MySqlConnection as LaravelMySqlConnection;
use Illuminate\Database\QueryException;

class MySqlConnection extends LaravelMySqlConnection
{
    use DetectsConcurrencyErrors;

    /**
     * Number of attempts to retry.
     */
    const ATTEMPTS_COUNT = 3;

    protected $async;

    /**
     * Get a new query builder instance.
     *
     * @return \Diviky\Bright\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array  $bindings
     * @param mixed  $useReadPdo
     *
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        if ($this->shouldQueue()) {
            $this->toQueue($query, $bindings);

            return true;
        }

        $prefix   = $this->getTablePrefix();
        $query    = \str_replace('#__', $prefix, $query);
        $type     = \trim(\strtolower(\explode(' ', $query)[0]));

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

    public function async($connection = null, $queue = null)
    {
        $this->async = [$connection, $queue];

        return $this;
    }

    public function toQueue($query, $bindings)
    {
        $async       = $this->async;
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
        $attempts_count = self::ATTEMPTS_COUNT;

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

    /**
     * Get the default query grammar instance.
     *
     * @return \Diviky\bright\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar();
        $grammar->setConfig($this->config['bright']);

        return $this->withTablePrefix($grammar);
    }

    protected function shouldQueue()
    {
        if (\is_array($this->async)) {
            return true;
        }

        return false;
    }
}
