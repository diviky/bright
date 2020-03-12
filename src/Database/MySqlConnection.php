<?php

namespace Karla\Database;

use Closure;
use Illuminate\Database\MySqlConnection as LaravelMySqlConnection;
use Illuminate\Database\QueryException;
use Karla\Database\DetectsConcurrencyErrors;
use Karla\Database\Query\Builder as QueryBuilder;
use Karla\Database\Query\Grammars\MySqlGrammar;

class MySqlConnection extends LaravelMySqlConnection
{
    use DetectsConcurrencyErrors;

    /**
     * Number of attempts to retry.
     */
    const ATTEMPTS_COUNT = 3;

    /**
     * Get a new query builder instance.
     *
     * @return \Karla\Database\Query\Builder
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
    public function statement($query, $bindings = [], $useReadPdo = false)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return true;
            }

            if ($useReadPdo) {
                $statement = $this->getPdoForSelect($useReadPdo)->prepare($query);
            } else {
                $statement = $this->getPdo()->prepare($query);
            }

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            return $statement->execute();
        });
    }

    /**
     * Run a SQL statement.
     *
     * @param string   $query
     * @param array    $bindings
     * @param \Closure $callback
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
     * @return \Karla\Database\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        $grammar = new MySqlGrammar();
        $grammar->setConfig($this->config['karla']);

        return $this->withTablePrefix($grammar);
    }
}
