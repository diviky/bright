<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use DeviceDetector\Cache\Cache;

/**
 * Class LaravelCacheBridge.
 */
class LaravelCacheBridge implements Cache
{
    /**
     * Contains the cache repository instance currently in use by the Laravel application.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Contains the configuration instance from Laravel.
     *
     * @var string
     */
    protected $prefix;

    /**
     * LaravelCacheBridge constructor.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache  the cache repository from Laravel
     * @param  mixed  $prefix
     */
    public function __construct(\Illuminate\Contracts\Cache\Repository $cache, $prefix = 'dd')
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    /**
     * Returns an item from the cache.
     *
     * @param  string  $id  the id to fetch from the cache
     * @return mixed
     */
    public function fetch($id)
    {
        return $this->cache->get($this->hashKey($id), false);
    }

    /**
     * Returns if a cached item exists or not.
     *
     * @param  string  $id  the id to fetch from the cache
     * @return bool
     */
    public function contains($id)
    {
        return $this->cache->has($this->hashKey($id));
    }

    /**
     * Stores data into the cache.
     *
     * @param  string  $id  the id to save into the cache
     * @param  mixed  $data  the data to save into the cache
     * @param  int  $lifeTime  the TTL for the data to remain in the cache
     */
    public function save($id, $data, $lifeTime = 0): void
    {
        if ($lifeTime >= 1) {
            $this->cache->put($this->hashKey($id), $data, $lifeTime);
        } else {
            $this->cache->forever($this->hashKey($id), $data);
        }
    }

    /**
     * Deletes a specific item from the cache.
     *
     * @param  string  $id  the id to delete from the cache
     * @return bool
     */
    public function delete($id)
    {
        return $this->cache->forget($this->hashKey($id));
    }

    /**
     * Deletes all cache tagged with device_detector.
     *
     * @return mixed
     */
    public function flushAll(): bool
    {
        return false;
    }

    /**
     * Hashes a given key into a format for DeviceDetector safe storage.
     *
     * @param  string  $key  the key to be hashed into DeviceDetector format
     */
    private function hashKey($key): string
    {
        return sprintf('%s:%s', $this->prefix, md5($key));
    }
}
