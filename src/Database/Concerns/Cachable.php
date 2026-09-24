<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use DateTime;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Contracts\Repository;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Query result caching for Bright's query builder.
 *
 * Enable caching on a connection via `bright.db_cache` or the connection's `cache` config key,
 * then chain `remember()` / `rememberForever()` before `get()` or `pluck()`.
 *
 * ## Basic usage
 *
 * ```php
 * $rows = DB::table('users')
 *     ->remember(600, 'active-users')
 *     ->where('active', true)
 *     ->get();
 * ```
 *
 * ## Request-scoped memoization (Laravel 13+)
 *
 * When the same cached query runs multiple times in one HTTP request or queued job, wrap the
 * underlying cache store with Laravel's memo driver so repeated reads do not hit Redis (or your
 * default store) again:
 *
 * Shorthand: use `rememberMemo()` instead of chaining `cacheMemo()` and `remember()`:
 *
 * ```php
 * $settings = DB::table('settings')
 *     ->rememberMemo(3600, 'app-settings')
 *     ->pluck('value', 'key');
 *
 * // Later in the same request — served from in-memory memo, not the remote cache store.
 * $settings = DB::table('settings')
 *     ->rememberMemo(3600, 'app-settings')
 *     ->pluck('value', 'key');
 * ```
 *
 * Combine with `cacheDriver()` to memoize a specific store:
 *
 * ```php
 * DB::table('tenants')
 *     ->cacheDriver('redis')
 *     ->cacheMemo()
 *     ->remember(300, 'tenant-list')
 *     ->get();
 * ```
 *
 * On Eloquent models that use {@see \Diviky\Bright\Database\Eloquent\Concerns\Cachable}, set
 * `protected bool $rememberCacheMemo = true;` to apply memoization to every query builder
 * created from that model.
 *
 * Call `cacheMemo(false)` or `dontRemember()` to disable memoization for a query.
 *
 * ## Stale-while-revalidate (Laravel 13+)
 *
 * Use `rememberFlexible()` to serve slightly stale cache while refreshing in the background after
 * the response is sent (same semantics as `Cache::flexible()`):
 *
 * ```php
 * $users = DB::table('users')
 *     ->rememberFlexible([300, 600], 'active-users')
 *     ->where('active', true)
 *     ->get();
 *
 * // Omit TTL for defaults (10 min fresh, 20 min stale — same base as `remember()`).
 * $users = DB::table('users')->rememberFlexible(null, 'active-users')->get();
 * ```
 *
 * Shorthand with memo: `rememberFlexibleMemo(null, 'active-users')`.
 *
 * On Eloquent models, set `protected array $rememberCacheFlexible = [300, 600];` instead of
 * `$rememberFor` when you want flexible caching on every query.
 */
trait Cachable
{
    /**
     * Default fresh window for flexible caching (matches `remember()` when seconds are omitted).
     */
    protected static int $defaultFlexibleFreshSeconds = 600;

    /**
     * Stale window multiplier applied when only the fresh TTL is provided (or when using defaults).
     */
    protected static int $defaultFlexibleStaleMultiplier = 2;

    /**
     * The key that should be used when caching the query.
     *
     * @var null|string
     */
    protected $cacheKey;

    /**
     * The number of seconds to cache the query.
     *
     * @var null|DateTime|int
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
     * Whether resolved cache values should be memoized for the current request/job.
     *
     * @var bool
     */
    protected $cacheMemo = false;

    /**
     * Fresh and stale TTL windows for flexible (stale-while-revalidate) caching.
     *
     * @var null|array{0: int, 1: int}
     */
    protected $cacheFlexibleTtl;

    /**
     * Optional lock configuration passed to the flexible cache refresher.
     *
     * @var null|array{seconds?: int, owner?: string}
     */
    protected $cacheFlexibleLock;

    /**
     * Whether flexible cache refresh should always be deferred.
     *
     * @var bool
     */
    protected $cacheFlexibleAlwaysDefer = false;

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
     * @return array|Collection
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
     * @param  Expression|string  $column
     * @param  null|string  $key
     * @return Collection
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

        $callback = $this->getCacheCallback($columns);

        return $this->resolveCachedValue($cacheKey, $callback);
    }

    /**
     * Execute the cached pluck query statement.
     *
     * @param  string  $column
     * @param  mixed  $key
     * @return Collection
     */
    public function pluckCached($column, $key = null)
    {
        $cacheKey = $this->getCacheKey();

        $callback = $this->pluckCacheCallback($column, $key);

        return $this->resolveCachedValue($cacheKey, $callback);
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param  null|DateTime|int  $seconds
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
     * Cache query results and memoize cache reads for the current request or job.
     *
     * Equivalent to `cacheMemo()->remember($seconds, $key)`.
     *
     * @param  null|DateTime|int  $seconds
     * @param  null|string  $key
     * @return $this
     */
    public function rememberMemo($seconds = null, $key = null)
    {
        return $this->cacheMemo()->remember($seconds, $key);
    }

    /**
     * Cache query results using Laravel's stale-while-revalidate flexible driver.
     *
     * Equivalent to `Cache::flexible($key, $ttl, $callback)` for this query.
     *
     * @param  null|array{0?: null|int, 1?: null|int}  $ttl  [fresh seconds, max stale seconds]
     * @param  null|string  $key
     * @param  null|array{seconds?: int, owner?: string}  $lock
     * @return $this
     */
    public function rememberFlexible(?array $ttl = null, $key = null, ?array $lock = null, bool $alwaysDefer = false)
    {
        $this->cacheFlexibleTtl = $this->normalizeFlexibleTtl($ttl);
        $this->cacheKey = $key;
        $this->cacheFlexibleLock = $lock;
        $this->cacheFlexibleAlwaysDefer = $alwaysDefer;

        return $this;
    }

    /**
     * Flexible cache with in-request memoization.
     *
     * @param  null|array{0?: null|int, 1?: null|int}  $ttl
     * @param  null|string  $key
     * @param  null|array{seconds?: int, owner?: string}  $lock
     * @return $this
     */
    public function rememberFlexibleMemo(?array $ttl = null, $key = null, ?array $lock = null, bool $alwaysDefer = false)
    {
        return $this->cacheMemo()->rememberFlexible($ttl, $key, $lock, $alwaysDefer);
    }

    /**
     * @return null|array{0: int, 1: int}
     */
    public function getCacheFlexibleTtl(): ?array
    {
        return $this->cacheFlexibleTtl;
    }

    /**
     * @return null|DateTime|int
     */
    public function getCacheTime()
    {
        return $this->cacheSeconds;
    }

    /**
     * Get the custom cache key name (without prefix), if one was set via remember().
     */
    public function getCacheKeyName(): ?string
    {
        return $this->cacheKey;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param  null|string  $key
     * @return Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * Indicate that the query should not be cached.
     *
     * @return Builder|static
     */
    public function dontRemember()
    {
        $this->cacheSeconds = $this->cacheKey = $this->cacheTags = null;
        $this->cacheMemo = false;
        $this->cacheFlexibleTtl = null;
        $this->cacheFlexibleLock = null;
        $this->cacheFlexibleAlwaysDefer = false;

        return $this;
    }

    /**
     * Indicate that the query should not be cached. Alias for dontRemember().
     *
     * @return Builder|static
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
     * Memoize cache reads for the current request or job using Laravel's memo cache driver.
     *
     * @return $this
     */
    public function cacheMemo(bool $memo = true)
    {
        $this->cacheMemo = $memo;

        return $this;
    }

    /**
     * Determine whether this query uses the memoized cache driver.
     */
    public function usesCacheMemo(): bool
    {
        return $this->cacheMemo;
    }

    /**
     * Set a global callable to modify the cache key.
     */
    public static function setCacheKeyModifier(?callable $modifier): void
    {
        self::$cacheKeyModifier = $modifier;
    }

    public static function modifyCacheKey(string $cacheKey): string
    {
        if (self::$cacheKeyModifier) {
            return call_user_func(self::$cacheKeyModifier, $cacheKey);
        }

        return $cacheKey;
    }

    /**
     * Get a unique cache key for the complete query.
     */
    public function getCacheKey(?string $appends = ''): string
    {
        $cache = $this->cachePrefix . ':' . ($this->cacheKey ?: $this->generateCacheKey($appends));

        return $this->applyCacheKeyModifier($cache);
    }

    public function namedCacheKey(string $name, string $appends = ''): string
    {
        return $this->applyCacheKeyModifier($this->cachePrefix . ':' . $name . $appends);
    }

    protected function applyCacheKeyModifier(string $cache): string
    {
        return self::modifyCacheKey($cache);
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
     * @return CacheManager
     */
    protected function getCache()
    {
        $cache = $this->getCacheDriver();

        return $this->cacheTags ? $cache->tags($this->cacheTags) : $cache;
    }

    /**
     * Get the cache driver.
     *
     * @return CacheRepository|Repository
     */
    protected function getCacheDriver()
    {
        /** @var CacheFactory&CacheManager $cache */
        $cache = app('cache');

        if ($this->cacheMemo && method_exists($cache, 'memo')) {
            return $cache->memo($this->cacheDriver);
        }

        return $cache->store($this->cacheDriver);
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
            $this->cacheFlexibleTtl = null;

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
            $this->cacheFlexibleTtl = null;

            return $this->pluck($column, $key);
        };
    }

    /**
     * @param  \Closure(): mixed  $callback
     * @return mixed
     */
    /**
     * @param  null|array{0?: null|int, 1?: null|int}  $ttl
     * @return array{0: int, 1: int}
     */
    protected function normalizeFlexibleTtl(?array $ttl): array
    {
        $fresh = static::$defaultFlexibleFreshSeconds;
        $stale = $fresh * static::$defaultFlexibleStaleMultiplier;

        if ($ttl === null || $ttl === []) {
            return [$fresh, $stale];
        }

        if (array_key_exists(0, $ttl) && $ttl[0] !== null) {
            $fresh = (int) $ttl[0];
        }

        if (array_key_exists(1, $ttl) && $ttl[1] !== null) {
            $stale = (int) $ttl[1];
        } elseif (array_key_exists(0, $ttl) && $ttl[0] !== null) {
            $stale = $fresh * static::$defaultFlexibleStaleMultiplier;
        }

        if ($stale <= $fresh) {
            $stale = $fresh * static::$defaultFlexibleStaleMultiplier;
        }

        return [$fresh, $stale];
    }

    protected function resolveCachedValue(string $cacheKey, \Closure $callback)
    {
        $cache = $this->getCache();

        if ($this->cacheFlexibleTtl !== null) {
            if (method_exists($cache, 'flexible')) {
                return $cache->flexible(
                    $cacheKey,
                    $this->cacheFlexibleTtl,
                    $callback,
                    $this->cacheFlexibleLock,
                    $this->cacheFlexibleAlwaysDefer
                );
            }

            return $cache->remember($cacheKey, $this->cacheFlexibleTtl[1], $callback);
        }

        $seconds = $this->cacheSeconds;

        if ($seconds instanceof DateTime || $seconds > 0) {
            return $cache->remember($cacheKey, $seconds, $callback);
        }

        return $cache->rememberForever($cacheKey, $callback);
    }

    /**
     * Check global cache enable or not.
     */
    protected function shouldCache(): bool
    {
        if (\is_null($this->cacheSeconds) && \is_null($this->cacheFlexibleTtl)) {
            return false;
        }

        return $this->connection->getConfig('bright.db_cache') ?? $this->connection->getConfig('cache') ?? false;
    }

    /**
     * Remember query with custom key.
     *
     * @param  string|null  $key
     * @param  int|null  $seconds
     * @return $this
     */
    public function rememberWithKey($key = null, $seconds = null)
    {
        return $this->remember($seconds, $key);
    }
}
