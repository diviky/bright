<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Jobs;

use Diviky\Bright\Database\BatchBuffer\BatchProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FlushBatchBucket implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 3600;

    public function __construct(
        protected string $bucketKey,
        protected ?int $limit = null
    ) {}

    public function uniqueId(): string
    {
        return $this->bucketKey;
    }

    public function handle(BatchProcessor $processor): void
    {
        $processor->flushBucket($this->bucketKey, $this->limit);
    }
}
