<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use DateTime;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

trait Cachable
{
    /**
     * The key that should be used when caching the query.
     *
     * @var null|string
     */
    protected $cacheKey;

    /**
     * The number of seconds to cache the query.
     *
     * @var null|\DateTime|int
     */
    protected $cacheSeconds;

    /**
     * The tags for the query cache.
     *
     * @var null|array
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
     * A global callable to modify the cache key.
     *
     * @var callable|null
     */
    protected static $cacheKeyModifier;

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|string  $columns
     * @return array|\Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        if ($this->shouldCache()) {
            return $this->getCached($columns);
        }

        $this->atomicEvent('select');

        return parent::get($columns);
    }

    /**
     * Get a collection instance containing the values of a given column.
     *
     * @param  \Illuminate\Contracts\Database\Query\Expression|string  $column
     * @param  null|string  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($column, $key = null)
    {
        if ($this->shouldCache()) {
            return $this->pluckCached($this->getExpressionValue($column), $this->getExpressionValue($key));
        }

        $this->atomicEvent('select');

        return parent::pluck($column, $key);
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $this->atomicEvent('select');

        return parent::exists();
    }

    /**
     * Execute the query as a cached "select" statement.
     *
     * @param  array|string  $columns
     * @return array
     */
    public function getCached($columns = ['*'])
    {
        if (empty($this->columns)) {
            $this->columns = $columns;
        }
        // If the query is requested to be cached, we will cache it using a unique key
        // for this database connection and query statement, including the bindings
        // that are used on this query, providing great convenience when caching.
        $cacheKey = $this->getCacheKey();

        $seconds = $this->cacheSeconds;

        $cache = $this->getCache();

        $callback = $this->getCacheCallback($columns);
        // If we've been given a DateTime instance or a "seconds" value that is
        // greater than zero then we'll pass it on to the remember method.
        // Otherwise we'll cache it indefinitely.
        if ($seconds instanceof \DateTime || $seconds > 0) {
            return $cache->remember($cacheKey, $seconds, $callback);
        }

        return $cache->rememberForever($cacheKey, $callback);
    }

    /**
     * Execute the cached pluck query statement.
     *
     * @param  string  $column
     * @param  mixed  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluckCached($column, $key = null)
    {
        $cacheKey = $this->getCacheKey();

        $seconds = $this->cacheSeconds;

        $cache = $this->getCache();

        $callback = $this->pluckCacheCallback($column, $key);

        if ($seconds instanceof \DateTime || $seconds > 0) {
            return $cache->remember($cacheKey, $seconds, $callback);
        }

        return $cache->rememberForever($cacheKey, $callback);
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param  null|\DateTime|int  $seconds
     * @param  string  $key
     * @return $this
     */
    public function remember($seconds = null, $key = null)
    {
        if (\is_null($seconds)) {
            $seconds = 10 * 60;
        }

        [$this->cacheSeconds, $this->cacheKey] = [$seconds, $key];

        return $this;
    }

    /**
     * @return null|\DateTime|int
     */
    public function getCacheTime()
    {
        return $this->cacheSeconds;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param  null|string  $key
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
        $this->cacheSeconds = $this->cacheKey = $this->cacheTags = null;

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
     * @param  array|mixed  $cacheTags
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
     * @param  string  $cacheDriver
     * @return $this
     */
    public function cacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Set a global callable to modify the cache key.
     */
    public static function setCacheKeyModifier(?callable $modifier): void
    {
        self::$cacheKeyModifier = $modifier;
    }

    /**
     * Get a unique cache key for the complete query.
     */
    public function getCacheKey(?string $appends = ''): string
    {
        $cache = $this->cachePrefix . ':' . ($this->cacheKey ?: $this->generateCacheKey($appends));

        if (self::$cacheKeyModifier) {
            $cache = call_user_func(self::$cacheKeyModifier, $cache);
        }

        return $cache;
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey(?string $appends = '')
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();

        return md5(serialize($sql) . serialize($bindings) . $appends);
    }

    /**
     * Flush the cache for the current model or a given tag name.
     *
     * @param  mixed  $cacheTags
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
     * @param  string  $prefix
     * @return $this
     */
    public function cachePrefix($prefix)
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
    protected function getCacheDriver(): CacheRepository
    {
        return app('cache')->store($this->cacheDriver);
    }

    /**
     * Get the Closure callback used when caching queries.
     *
     * @param  array|string  $columns
     * @return \Closure()
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns) {
            $this->cacheSeconds = null;

            return $this->get($columns);
        };
    }

    /**
     * Get the callback for pluck queries.
     *
     * @param  string  $column
     * @param  mixed  $key
     * @return \Closure()
     */
    protected function pluckCacheCallback($column, $key = null)
    {
        return function () use ($column, $key) {
            $this->cacheSeconds = null;

            return $this->pluck($column, $key);
        };
    }

    /**
     * Check global cache enable or not.
     */
    protected function shouldCache(): bool
    {
        if (\is_null($this->cacheSeconds)) {
            return false;
        }

        return $this->connection->getConfig('bright.db_cache') ?? $this->connection->getConfig('cache') ?? false;
    }

    /**
     * Remember query with custom key.
     *
     * @param string|null $key
     * @param int|null $seconds
     * @return $this
     */
    public function rememberWithKey($key = null, $seconds = null)
    {
        return $this->remember($seconds, $key);
    }
}
