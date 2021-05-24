<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sharding\IdGenerators;

interface IdGeneratorInterface
{
    public function getNextId(): int;

    public function getLastId(): int;

    public function increment(): int;
}
