<?php

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

use Diviky\Bright\Models\Sharding;

class DatabaseCentralTable implements ShardChooserInterface
{
    private $connections = [];
    private $relationKey = '';

    public function __construct($connections, $relationKey = null)
    {
        $this->connections = $connections;
        $this->relationKey = $relationKey;
    }

    public function getShardById($id)
    {
        return Sharding::where('user_id', $id)
            ->where('status', 1)
            ->remember(10 * 60 * 60, "{$this->relationKey}:{$id}")
            ->value('connection');
    }

    public function chooseShard($id)
    {
        return $this->getShardById($id);
    }
}
