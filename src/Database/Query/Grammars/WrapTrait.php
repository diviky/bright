<?php

namespace Diviky\Bright\Database\Query\Grammars;

trait WrapTrait
{
    protected $config = [];

    public function setConfig($config = [])
    {
        if (\is_array($config)) {
            $this->config = \array_merge($this->config, $config);
        }
    }

    /**
     * Wrap a table in keyword identifiers.
     *
     * @param \Illuminate\Database\Query\Expression|string $table
     *
     * @return string
     */
    public function wrapTable($table)
    {
        if (!$this->isExpression($table)) {
            if (false !== \strpos($table, '.')) {
                list($database, $table) = \explode('.', $table);

                return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $table, true);
            }

            $databases = $this->config['databases'];

            $alias = '';
            if (false !== \stripos($table, ' as ')) {
                $segments = \preg_split('/\s+as\s+/i', $table);
                $alias    = ' as ' . $segments[1];
                $table    = $segments[0];
            }

            if (\is_array($databases) && isset($databases[$table])) {
                return $this->wrap($databases[$table]) . '.' . $this->wrap($this->tablePrefix . $table . $alias, true);
            }

            return $this->wrap($this->tablePrefix . $table . $alias, true);
        }

        return $this->getValue($table);
    }
}
