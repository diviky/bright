<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

interface ShardChooserInterface
{
    /**
     * Get the shard.
     *
     * @param int|string $id
     *
     * @return string
     */
    public function getShardById($id);

    /**
     * Choose the shard.
     *
     * @param string $id
     *
     * @return string
     */
    public function chooseShard($id);

    /**
     * Set the sharding relation.
     *
     * @param string $id
     * @param string $shard
     */
    public function setRelation($id, $shard): bool;
}
