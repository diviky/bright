<?php

namespace Diviky\Bright\Database\Sharding\IdGenerators;

interface IdGeneratorInterface
{
    public function getNextId(): int;

    public function getLastId(): int;

    public function increment(): int;
}
