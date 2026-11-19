<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Diviky\Bright\Console\Command;
use Diviky\Bright\Database\BatchBuffer\BatchProcessor;
use Diviky\Bright\Database\BatchBuffer\RedisPipeline;

class BatchProcess extends Command
{
    protected $signature = 'bright:batch-process
        {--bucket= : Flush a single bucket key only}
        {--once : Exit after one pass}
        {--limit= : Override chunk size when claiming from Redis}';

    protected $description = 'Read batched insert/update payloads from Redis and flush eligible buckets to the database';

    public function handle(BatchProcessor $processor, RedisPipeline $pipeline): int
    {
        $config = config('bright.async.batch', []);
        $registryKey = (string) ($config['registry_key'] ?? 'bright:batch:registry');
        $maxSize = (int) ($config['max_size'] ?? 1000);
        $maxWait = (int) ($config['max_wait_seconds'] ?? 5);
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $now = time();

        $bucket = $this->option('bucket');
        $keys = is_string($bucket) && $bucket !== ''
            ? [$bucket]
            : $pipeline->registryMembers($registryKey);

        $bucketsTouched = 0;
        $rowsFlushed = 0;

        foreach ($keys as $bucketKey) {
            if (!$this->isEligible($pipeline, $bucketKey, $maxSize, $maxWait, $now)) {
                continue;
            }

            $flushed = $processor->flushBucket($bucketKey, $limit);

            if ($flushed > 0) {
                $bucketsTouched++;
                $rowsFlushed += $flushed;
            }

            if ($this->option('once')) {
                break;
            }
        }

        $this->info("Buckets flushed: {$bucketsTouched}; rows processed: {$rowsFlushed}");

        return self::SUCCESS;
    }

    protected function isEligible(RedisPipeline $pipeline, string $bucketKey, int $maxSize, int $maxWait, int $now): bool
    {
        $length = $pipeline->length($bucketKey);

        if ($length >= $maxSize) {
            return true;
        }

        if ($length === 0) {
            return false;
        }

        $headQueuedAt = $pipeline->headQueuedAt($bucketKey);

        if ($headQueuedAt === null) {
            return false;
        }

        return ($now - $headQueuedAt) >= $maxWait;
    }

    public static function defaultCommandName(): string
    {
        return (string) config('bright.async.batch.command', 'bright:batch-process');
    }
}
