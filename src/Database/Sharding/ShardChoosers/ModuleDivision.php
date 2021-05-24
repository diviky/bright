<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

class ModuleDivision implements ShardChooserInterface
{
    /**
     * Set the connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * @param array  $connections
     * @param string $relationKey
     */
    public function __construct($connections, $relationKey = null)
    {
        $this->connections = $connections;

        unset($relationKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getShardById($id)
    {
        return $this->connections[intval($id) % \count($this->connections)];
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
