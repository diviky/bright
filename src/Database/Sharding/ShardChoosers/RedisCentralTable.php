<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

use Diviky\Bright\Database\Sharding\Exceptions\ShardingException;
use Illuminate\Support\Facades\Redis;

class RedisCentralTable implements ShardChooserInterface
{
    /**
     * Set the connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Cache key.
     *
     * @var string
     */
    protected $relationKey = '';

    /**
     * @param  array  $connections
     * @param  string  $relationKey
     */
    public function __construct($connections, $relationKey)
    {
        $this->connections = $connections;
        if (!$relationKey) {
            throw new ShardingException('You should set "relation_key" param in config to use "Central Table" sharding');
        }
        $this->relationKey = $relationKey;
    }

    public function getShardById($id)
    {
        return Redis::get("{$this->relationKey}:{$id}");
    }

    public function chooseShard($id)
    {
        return $this->connections[intval($id) % \count($this->connections)];
    }

    public function setRelation($id, $shard): bool
    {
        return Redis::set("{$this->relationKey}:{$id}", $shard);
    }
}
