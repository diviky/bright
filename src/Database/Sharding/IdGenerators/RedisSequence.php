<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\IdGenerators;

use Illuminate\Support\Facades\Redis;

class RedisSequence implements IdGeneratorInterface
{
    /**
     * @var int
     */
    protected $sequenceKey;

    /**
     * @param  int  $sequenceKey
     */
    public function __construct($sequenceKey)
    {
        $this->sequenceKey = $sequenceKey;
    }

    public function getNextId(): int
    {
        return (int) Redis::get($this->sequenceKey) + 1;
    }

    public function getLastId(): int
    {
        return (int) Redis::get($this->sequenceKey);
    }

    /**
     * Set the last id.
     *
     * @param  int  $id
     */
    public function setLastId($id): bool
    {
        return Redis::set($this->sequenceKey, $id);
    }

    public function increment(): int
    {
        return Redis::incr($this->sequenceKey);
    }
}
