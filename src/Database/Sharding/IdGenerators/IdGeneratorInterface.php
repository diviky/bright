<?php

namespace Diviky\Bright\Database\Sharding\IdGenerators;

interface IdGeneratorInterface
{
    public function getNextId();
}
