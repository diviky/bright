<?php

namespace Diviky\Bright\Database\Sharding\ShardChoosers;

interface ShardChooserInterface
{
    public function getShardById($id);

    public function chooseShard($id);
}
