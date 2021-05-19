<?php

namespace Diviky\Bright\Database\Traits;

use DateTime;

trait Cachable
{
    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of seconds to cache the query.
     *
     * @var int
     */
    protected $cacheseconds;

    /**
     * The tags for the query cache.
     *
     * @var array
     */
    protected $cacheTags;

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    protected $cacheDriver;

    /**
     * A cache prefix.
     *
     * @var string
     */
    protected $cachePrefix = 'sql';

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        if (!\is_null($this->cacheseconds) && $this->cacheEnabled()) {
            return $this->getCached($columns);
        }

        $this->removeWheres();

        return parent::get($columns);
    }

    /**
     * Execute the query as a cached "select" statement.
     *
     * @param array $columns
     *
     * @return array
     */
    public function getCached($columns = ['*'])
    {
        if (\is_null($this->columns)) {
            $this->columns = $columns;
        }
        // If the query is requested to be cached, we will cache it using a unique key
        // for this database connection and query statement, including the bindings
        // that are used on this query, providing great convenience when caching.
        list($key, $seconds) = $this->getCacheInfo();

        $cache    = $this->getCache();
        $callback = $this->getCacheCallback($columns);
        // If we've been given a DateTime instance or a "seconds" value that is
        // greater than zero then we'll pass it on to the remember method.
        // Otherwise we'll cache it indefinitely.
        if ($seconds instanceof DateTime || $seconds > 0) {
            return $cache->remember($key, $seconds, $callback);
        }

        return $cache->rememberForever($key, $callback);
    }

    /**
     * Execute the pluck query statement.
     *
     * @param string $column
     * @param mixed  $key
     *
     * @return array|static[]
     */
    public function pluck($column, $key = null)
    {
        if (!is_null($this->cacheSeconds)) {
            return $this->pluckCached($column, $key);
        }

        return parent::pluck($column, $key);
    }

    /**
     * Execute the cached pluck query statement.
     *
     * @param string $column
     * @param mixed  $key
     *
     * @return array
     */
    public function pluckCached($column, $key = null)
    {
        $cacheKey = $this->getCacheKey($column . $key);

        $seconds = $this->cacheSeconds;

        $cache = $this->getCache();

        $callback = $this->pluckCacheCallback($column, $key);

        if ($seconds instanceof DateTime || $seconds > 0) {
            return $cache->remember($cacheKey, $seconds, $callback);
        }

        return $cache->rememberForever($cacheKey, $callback);
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param null|\DateTime|int $seconds
     * @param string             $key
     *
     * @return $this
     */
    public function remember($seconds = null, $key = null)
    {
        if (\is_null($seconds)) {
            $seconds = 10 * 60;
        }

        list($this->cacheseconds, $this->cacheKey) = [$seconds, $key];

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param null|string $key
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * Indicate that the query should not be cached.
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function dontRemember()
    {
        $this->cacheseconds = $this->cacheKey = $this->cacheTags = null;

        return $this;
    }

    /**
     * Indicate that the query should not be cached. Alias for dontRemember().
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function doNotRemember()
    {
        return $this->dontRemember();
    }

    /**
     * Indicate that the results, if cached, should use the given cache tags.
     *
     * @param array|mixed $cacheTags
     *
     * @return $this
     */
    public function cacheTags($cacheTags)
    {
        $this->cacheTags = $cacheTags;

        return $this;
    }

    /**
     * Indicate that the results, if cached, should use the given cache driver.
     *
     * @param string $cacheDriver
     *
     * @return $this
     */
    public function cacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        $cache = $this->cachePrefix . ':' . ($this->cacheKey ?: $this->generateCacheKey());

        return \str_replace(':uid:', user('id'), $cache);
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        $name = $this->connection->getName();
        $key  = $name . $this->toSql() . \serialize($this->getBindings());

        return \hash('sha256', $key);
    }

    /**
     * Flush the cache for the current model or a given tag name.
     *
     * @param mixed $cacheTags
     *
     * @return bool
     */
    public function flushCache($cacheTags = null)
    {
        $cache = $this->getCacheDriver();
        if (!\method_exists($cache, 'tags')) {
            return false;
        }

        $cacheTags = $cacheTags ?: $this->cacheTags;
        $cache->tags($cacheTags)->flush();

        return true;
    }

    /**
     * Set the cache prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->cachePrefix = $prefix;

        return $this;
    }

    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function getCache()
    {
        $cache = $this->getCacheDriver();

        return $this->cacheTags ? $cache->tags($this->cacheTags) : $cache;
    }

    /**
     * Get the cache driver.
     */
    protected function getCacheDriver(): \Illuminate\Contracts\Cache\Repository
    {
        return app('cache')->driver($this->cacheDriver);
    }

    /**
     * Get the cache key and cache seconds as an array.
     *
     * @return array
     */
    protected function getCacheInfo()
    {
        return [$this->getCacheKey(), $this->cacheseconds];
    }

    /**
     * Get the Closure callback used when caching queries.
     *
     * @param array $columns
     *
     * @return \Closure
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns) {
            $this->cacheseconds = null;

            return $this->get($columns);
        };
    }

    protected function cacheEnabled()
    {
        return $this->connection->getConfig('bright.db_cache') ?: $this->connection->getConfig('cache');
    }
}
