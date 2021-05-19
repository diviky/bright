<?php

namespace Diviky\Bright\Database\Sharding;

/**
 * Class ShardManager.
 */
class ShardManager
{
    private $shardChooser;

    private $idGenerator;

    private $mapManager;

    public function __construct(MapManager $mapManager)
    {
        $this->mapManager = $mapManager;
    }

    public function setService($name = 'default'): static
    {
        $this->mapManager->setService($name);
        $this->shardChooser = $this->mapManager->getShardChooser();
        $this->idGenerator  = $this->mapManager->getidGenerator();

        return $this;
    }

    public function getShardConfig()
    {
        return $this->mapManager->getServiceConfig();
    }

    public function getShardById($id)
    {
        return $this->shardChooser->getShardById($id);
    }

    public function getIdsFromShard($firstId, $lastId, $shard)
    {
        return $this->shardChooser->getIdsFromShard($firstId, $lastId, $shard);
    }

    public function getLastId()
    {
        return $this->idGenerator->getLastId();
    }

    public function getNextId()
    {
        return $this->idGenerator->getNextId();
    }

    public function increment()
    {
        return $this->idGenerator->increment();
    }

    public function chooseShard($id)
    {
        return $this->shardChooser->chooseShard($id);
    }

    public function setRelation($id, $shard)
    {
        return $this->shardChooser->setRelation($id, $shard);
    }
}
