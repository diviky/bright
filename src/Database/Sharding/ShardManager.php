<?php

namespace Diviky\Bright\Database\Sharding;

use Diviky\Bright\Database\Sharding\IdGenerators\IdGeneratorInterface;
use Diviky\Bright\Database\Sharding\ShardChoosers\ShardChooserInterface;

/**
 * Class ShardManager.
 */
class ShardManager
{
    /**
     * Shard chooser.
     *
     * @var ShardChooserInterface
     */
    protected $shardChooser;

    /**
     * Id generator.
     *
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var MapManager
     */
    protected $mapManager;

    public function __construct(MapManager $mapManager)
    {
        $this->mapManager = $mapManager;
    }

    /**
     * Set the sharding namr.
     *
     * @param string $name
     */
    public function setService($name = 'default'): static
    {
        $this->mapManager->setService($name);
        $this->shardChooser = $this->mapManager->getShardChooser();
        $this->idGenerator  = $this->mapManager->getidGenerator();

        return $this;
    }

    /**
     * Get the shard config.
     *
     * @return array
     */
    public function getShardConfig()
    {
        return $this->mapManager->getServiceConfig();
    }

    /**
     * Get the shard.
     *
     * @param int|string $id
     *
     * @return string
     */
    public function getShardById($id)
    {
        return $this->shardChooser->getShardById($id);
    }

    public function getLastId(): int
    {
        return $this->idGenerator->getLastId();
    }

    public function getNextId(): int
    {
        return $this->idGenerator->getNextId();
    }

    public function increment(): int
    {
        return $this->idGenerator->increment();
    }

    /**
     * Choose the shard.
     *
     * @param string $id
     */
    public function chooseShard($id): string
    {
        return $this->shardChooser->chooseShard($id);
    }

    /**
     * Set the sharding relation.
     *
     * @param string $id
     * @param string $shard
     *
     * @return bool
     */
    public function setRelation($id, $shard)
    {
        return $this->shardChooser->setRelation($id, $shard);
    }
}
