<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Database\Batch as DatabaseBatch;

trait Batch
{
    public function batch(bool $bulk = false): DatabaseBatch
    {
        $batch = new DatabaseBatch($this);
        $batch->bulk($bulk);

        return $batch;
    }
}
