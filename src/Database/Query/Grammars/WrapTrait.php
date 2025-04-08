<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Expression;

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
     * {@inheritDoc}
     */
    public function wrapTable($table, $prefix = null)
    {
        if ($this->isExpression($table)) {
            return parent::wrapTable($table);
        }

        $table = $this->getExpressionValue($table);

        if (str_starts_with($table, 'laravel_reserved')) {
            return $table;
        }

        if ($this->connection) {
            $prefix ??= $this->connection->getTablePrefix();
        }

        if (\strpos($table, '.') !== false) {
            [$database, $table] = \explode('.', $table);
            $table = preg_replace('/^' . $prefix . '/', '', $table);

            return $this->wrap($database) . '.' . $this->wrap($prefix . $table, true);
        }

        $alias = '';
        if (\stripos($table, ' as ') !== false) {
            $segments = \preg_split('/\s+as\s+/i', $table);
            $alias = ' as ' . $segments[1];
            $table = $segments[0];
        }

        $table = preg_replace('/^' . $prefix . '/', '', $table);

        $databases = $this->config['databases'] ?? [];
        if (\is_array($databases)) {
            if (isset($databases['names']) && \is_array($databases['names']) && isset($databases['names'][$table])) {
                return $this->wrap($databases['names'][$table]) . '.' . $this->wrap($prefix . $table . $alias);
            }

            $patterns = $databases['patterns'] ?? [];
            if (\is_array($patterns)) {
                foreach ($patterns as $pattern => $database) {
                    if (preg_match('/^' . $pattern . '/', $table)) {
                        if (\is_array($database)) {
                            $database = $database[0];
                        }

                        return $this->wrap($database) . '.' . $this->wrap($prefix . $table . $alias);
                    }
                }
            }
        }

        return $this->wrap($prefix . $table . $alias);
    }

    /**
     * get the value from expression.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $value
     * @return mixed
     */
    protected function getExpressionValue($value)
    {
        if ($value instanceof Expression) {
            return (string) $value->getValue($this);
        }

        return $value;
    }
}
