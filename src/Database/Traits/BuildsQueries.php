<?php

namespace Diviky\Bright\Database\Traits;

use Illuminate\Support\LazyCollection;
use InvalidArgumentException;

trait BuildsQueries
{
    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int $chunkSize
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function flatChunk($chunkSize = 1000, callable $callback = null)
    {
        if ($chunkSize < 1) {
            throw new InvalidArgumentException('The chunk size should be at least 1');
        }

        return LazyCollection::make(function () use ($chunkSize, $callback) {
            $page = 1;

            while (true) {
                $results = $this->forPage($page++, $chunkSize)->get();

                if ($callback) {
                    foreach ($results as $result) {
                        yield $result = $callback($result);
                    }
                } else {
                    // Flatten the chunks out
                    foreach ($results as $result) {
                        yield $result;
                    }
                }

                if ($results->count() < $chunkSize) {
                    return;
                }
            }
        });
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int        $chunkSize
     * @param null|mixed $callback
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Support\LazyCollection
     *
     * @deprecated 2.0
     */
    public function iterate($chunkSize = 10000, $callback = null)
    {
        return $this->flatChunk($chunkSize, $callback);
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param int $chunkSize
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function iterator($chunkSize = 10000, callable $callback = null)
    {
        return $this->flatChunk($chunkSize, $callback);
    }
}
