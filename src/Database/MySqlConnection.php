<?php

namespace Karla\Database;

use Closure;
use Illuminate\Database\MySqlConnection as LaravelMySqlConnection;
use Illuminate\Database\QueryException;
use Karla\Database\Query\Builder as QueryBuilder;
use Karla\Database\Query\Grammars\MySqlGrammar;

class MySqlConnection extends LaravelMySqlConnection
{
    /**
     * Number of attempts to retry.
     */
    const ATTEMPTS_COUNT = 3;

    /**
     * Run a SQL statement.
     *
     * @param string   $query
     * @param array    $bindings
     * @param \Closure $callback
     *
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        $attempts_count = self::ATTEMPTS_COUNT;

        for ($attempt = 1; $attempt <= $attempts_count; ++$attempt) {
            try {
                return parent::runQueryCallback($query, $bindings, $callback);
            } catch (QueryException $e) {
                if ($attempt > $attempts_count) {
                    throw $e;
                }

                if (!$this->causedByDeadlock($e)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Karla\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Karla\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new MySqlGrammar());
    }
}
