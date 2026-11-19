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
        if (isset($config['databases']) && \is_array($config['databases'])) {
            $config = $config['databases'];
        }

        $this->config['databases'] = \array_merge($this->config['databases'] ?? [], $config);
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

            return $this->wrap($this->resolveDatabaseName($database)) . '.' . $this->wrapRoutedTableName($table);
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
                return $this->wrap($this->resolveDatabaseName($databases['names'][$table])) . '.' . $this->wrapRoutedTableName($table, $alias);
            }

            $patterns = $databases['patterns'] ?? [];
            if (\is_array($patterns)) {
                foreach ($patterns as $pattern => $database) {
                    if (preg_match('/^' . $pattern . '/', $table)) {
                        return $this->wrap($this->resolveDatabaseName($database)) . '.' . $this->wrapRoutedTableName($table, $alias);
                    }
                }
            }
        }

        // On a non-default connection (e.g. blog → airo_blog), qualify
        // unmatched tables with the default database (e.g. tags → airo.tags).
        $defaultDatabase = $this->getDefaultDatabaseName();
        $currentDatabase = $this->connection?->getDatabaseName();

        if (
            \is_string($defaultDatabase)
            && $defaultDatabase !== ''
            && \is_string($currentDatabase)
            && $currentDatabase !== ''
            && $defaultDatabase !== $currentDatabase
        ) {
            return $this->wrap($this->resolveDatabaseName($defaultDatabase)) . '.' . $this->wrapRoutedTableName($table, $alias);
        }

        return $this->wrap($prefix . $table . $alias);
    }

    /**
     * Wrap a table segment qualified onto a routed database name.
     *
     * Connection table prefixes apply to the default database only; routed schemas
     * already encode tenant/schema naming in the database identifier.
     */
    protected function wrapRoutedTableName(string $table, string $alias = ''): string
    {
        return $this->wrap($table . $alias);
    }

    /**
     * Resolve a configured database name to a string for identifier wrapping.
     */
    protected function resolveDatabaseName(mixed $database): string
    {
        if (\is_array($database)) {
            $database = $database[0] ?? '';
        }

        return (string) $database;
    }

    /**
     * Default connection database name for cross-schema table qualification.
     */
    protected function getDefaultDatabaseName(): ?string
    {
        $default = \config('database.default');

        if (!\is_string($default) || $default === '') {
            return null;
        }

        $database = \config("database.connections.{$default}.database");

        return \is_string($database) && $database !== '' ? $database : null;
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
