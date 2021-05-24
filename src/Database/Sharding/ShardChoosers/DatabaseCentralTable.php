<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

use Diviky\Bright\Models\Sharding;

class DatabaseCentralTable implements ShardChooserInterface
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
     * @var null|string
     */
    protected $relationKey = '';

    /**
     * @param array  $connections
     * @param string $relationKey
     */
    public function __construct($connections, $relationKey = null)
    {
        $this->connections = $connections;
        $this->relationKey = $relationKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getShardById($id)
    {
        return Sharding::where('user_id', $id)
            ->where('status', 1)
            ->remember(10 * 60 * 60, "{$this->relationKey}:{$id}")
            ->value('connection');
    }

    /**
     * {@inheritDoc}
     */
    public function chooseShard($id)
    {
        return $this->getShardById($id);
    }

    /**
     * {@inheritDoc}
     */
    public function setRelation($id, $shard): bool
    {
        return true;
    }
}
