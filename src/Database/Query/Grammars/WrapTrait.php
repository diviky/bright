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
     * @param \Illuminate\Database\Query\Expression|string $name
     *
     * @return string
     */
    public function wrapTable($name)
    {
        if (!is_string($name)) {
            return $this->getValue($name);
        }

        if (false !== \strpos($name, '.')) {
            list($database, $name) = \explode('.', $name);
            $name = preg_replace('/^' . $this->tablePrefix . '/', '', $name);

            return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $name, true);
        }

        $databases = $this->config['databases'];

        $alias = '';
        if (false !== \stripos($name, ' as ')) {
            $segments = \preg_split('/\s+as\s+/i', $name);
            $alias = ' as ' . $segments[1];
            $name = $segments[0];
        }

        $name = preg_replace('/^' . $this->tablePrefix . '/', '', $name);

        if (\is_array($databases) && isset($databases[$name])) {
            return $this->wrap($databases[$name]) . '.' . $this->wrap($this->tablePrefix . $name . $alias, true);
        }

        $patterns = $this->config['database_patterns'];
        if (\is_array($patterns)) {
            foreach ($patterns as $pattern => $database) {
                if (preg_match('/^' . $pattern . '/', $name)) {
                    return $this->wrap($database) . '.' . $this->wrap($this->tablePrefix . $name . $alias, true);
                }
            }
        }

        return $this->wrap($this->tablePrefix . $name . $alias, true);
    }
}
