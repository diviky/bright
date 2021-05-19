<?php

namespace Diviky\Bright\Database\Sharding\IdGenerators;

class RedisSequence implements IdGeneratorInterface
{
    private $sequenceKey;

    public function __construct($sequenceKey)
    {
        $this->sequenceKey = $sequenceKey;
    }

    /**
     * @return int
     */
    public function getNextId()
    {
        return (int) \Redis::get($this->sequenceKey) + 1;
    }

    public function getLastId(): int
    {
        return (int) \Redis::get($this->sequenceKey);
    }

    public function setLastId($id): bool
    {
        return \Redis::set($this->sequenceKey, $id);
    }

    public function increment(): int
    {
        return \Redis::incr($this->sequenceKey);
    }
}
