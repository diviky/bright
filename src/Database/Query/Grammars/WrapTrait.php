<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

trait WrapTrait
{
    /**
     * Set the builder config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Set the builder config.
     */
    public function setConfig(array $config = []): void
    {
        $this->config = \array_merge($this->config, $config);
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
        if (!is_string($table)) {
            return $this->getValue($table);
        }

        if (false !== \strpos($table, '.')) {
            list($database, $table) = \explode('.', $table);
            $table = preg_replace('/^' . $this->tablePrefix . '/', '', $table);

            return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $table, true);
        }

        $alias = '';
        if (false !== \stripos($table, ' as ')) {
            $segments = \preg_split('/\s+as\s+/i', $table);
            $alias = ' as ' . $segments[1];
            $table = $segments[0];
        }

        $table = preg_replace('/^' . $this->tablePrefix . '/', '', $table);

        $databases = $this->config['databases'];
        if (\is_array($databases)) {
            if (isset($databases['names']) && \is_array($databases['names']) && isset($databases['names'][$table])) {
                return $this->wrap($databases['names'][$table]) . '.' . $this->wrap($this->tablePrefix . $table . $alias, true);
            }

            $patterns = $databases['patterns'] ?? [];
            if (\is_array($patterns)) {
                foreach ($patterns as $pattern => $database) {
                    if (preg_match('/^' . $pattern . '/', $table)) {
                        if (\is_array($database)) {
                            $database = $database[0];
                        }

                        return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $table . $alias, true);
                    }
                }
            }
        }

        return $this->wrap($this->tablePrefix . $table . $alias, true);
    }
}
