<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;

trait RememberablePaginationCount
{
    public static bool $automaticallyRememberCount = false;

    public static int $automaticallyRememberCountSeconds = 60;

    protected bool $paginationCountCacheDisabled = false;

    protected bool $paginationCountCacheExplicit = false;

    protected ?int $paginationCountCacheSeconds = null;

    protected ?string $paginationCountCacheKey = null;

    /**
     * Enable or disable cached pagination totals for all Bright Eloquent paginate() calls.
     */
    public static function automaticallyRememberCount(bool $remember = true, int $seconds = 60): void
    {
        static::$automaticallyRememberCount = $remember;
        static::$automaticallyRememberCountSeconds = $seconds;
    }

    public static function automaticPaginationCountCachingEnabled(): bool
    {
        return static::$automaticallyRememberCount && static::$automaticallyRememberCountSeconds > 0;
    }

    public static function automaticPaginationCountCacheSeconds(): int
    {
        return static::$automaticallyRememberCountSeconds;
    }

    /**
     * @return $this
     */
    public function rememberCount(?int $seconds = null, ?string $key = null): static
    {
        $this->paginationCountCacheExplicit = true;
        $this->paginationCountCacheDisabled = false;
        $this->paginationCountCacheSeconds = $seconds;
        $this->paginationCountCacheKey = $key;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontRememberCount(): static
    {
        $this->paginationCountCacheDisabled = true;
        $this->paginationCountCacheExplicit = false;
        $this->paginationCountCacheSeconds = null;
        $this->paginationCountCacheKey = null;

        return $this;
    }

    public function shouldRememberCount(): bool
    {
        if ($this->paginationCountCacheDisabled) {
            return false;
        }

        if ($this->paginationCountCacheExplicit) {
            return true;
        }

        return static::$automaticallyRememberCount && static::$automaticallyRememberCountSeconds > 0;
    }

    public function getCountForPaginationCached(): int
    {
        $seconds = $this->resolvePaginationCountCacheSeconds();

        $cacheKey = $this->resolvePaginationCountCacheKey();

        return (int) Cache::remember($cacheKey, $seconds, function (): int {
            return (int) $this->paginationCountQueryBuilder()->getCountForPagination();
        });
    }

    /**
     * @param  array<int, string>|string  $columns
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null): LengthAwarePaginator
    {
        if ($total === null && $this->shouldRememberCount()) {
            $total = $this->getCountForPaginationCached();
        }

        return parent::paginate($perPage, $columns, $pageName, $page, $total);
    }

    public function copyPaginationCountSettings(self $from): void
    {
        $this->paginationCountCacheDisabled = $from->paginationCountCacheDisabled;
        $this->paginationCountCacheExplicit = $from->paginationCountCacheExplicit;
        $this->paginationCountCacheSeconds = $from->paginationCountCacheSeconds;
        $this->paginationCountCacheKey = $from->paginationCountCacheKey;
    }

    protected function resolvePaginationCountCacheSeconds(): int
    {
        if ($this->paginationCountCacheExplicit && $this->paginationCountCacheSeconds !== null) {
            return $this->paginationCountCacheSeconds;
        }

        if ($this->paginationCountCacheExplicit && $this->paginationCountCacheSeconds === null) {
            return static::$automaticallyRememberCountSeconds > 0
                ? static::$automaticallyRememberCountSeconds
                : 60;
        }

        return static::$automaticallyRememberCountSeconds;
    }

    protected function resolvePaginationCountCacheKey(): string
    {
        $countQuery = $this->paginationCountQueryBuilder();

        if ($this->paginationCountCacheKey !== null) {
            $fingerprint = $countQuery->generateCacheKey(':count');

            return $countQuery->namedCacheKey($this->paginationCountCacheKey . ':' . $fingerprint, '');
        }

        return $countQuery->getCacheKey(':count');
    }

    /**
     * Query builder state used for pagination COUNT(*) and its cache key.
     */
    protected function paginationCountQueryBuilder(): Builder
    {
        return (clone $this)->setEagerLoads([])->toBase();
    }
}
