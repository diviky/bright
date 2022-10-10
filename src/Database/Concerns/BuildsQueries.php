<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Helpers\Iterator\SelectIterator;
use InvalidArgumentException;

trait BuildsQueries
{
    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int $chunkSize
     *
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \InvalidArgumentException
     */
    public function lazyMap($chunkSize = 1000, callable $callback = null)
    {
        if (isset($callback)) {
            return $this->lazy($chunkSize)->map(function ($item, $key) use ($callback) {
                return $callback($item, $key);
            });
        }

        return $this->lazy($chunkSize);
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int $chunkSize
     *
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \InvalidArgumentException
     */
    public function flatChunk($chunkSize = 1000, callable $callback = null)
    {
        return $this->lazyMap($chunkSize, $callback);
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int        $chunkSize
     * @param null|mixed $callback
     *
     * @return \Iterator
     *
     * @throws \InvalidArgumentException
     */
    public function iterate($chunkSize = 10000, $callback = null)
    {
        if ($chunkSize < 1) {
            throw new InvalidArgumentException('The chunk size should be at least 1');
        }

        return new SelectIterator($this, $chunkSize, $callback);
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int $chunkSize
     *
     * @return \Iterator
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated 2.0
     */
    public function iterator($chunkSize = 10000, callable $callback = null)
    {
        return $this->iterate($chunkSize, $callback);
    }
}
