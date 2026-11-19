<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

use Diviky\Bright\Database\Jobs\FlushBatchBucket;
use Illuminate\Support\Facades\DB;
use Throwable;

class BatchProcessor
{
    public function __construct(
        protected RedisPipeline $pipeline,
        protected InsertBatcher $insertBatcher,
        protected UpdateBatcher $updateBatcher
    ) {}

    public function flushBucket(string $bucketKey, ?int $limit = null): int
    {
        $config = config('bright.async.batch', []);
        $limit = $limit ?? (int) ($config['max_size'] ?? 1000);
        $registryKey = (string) ($config['registry_key'] ?? 'bright:batch:registry');

        $claimed = $this->pipeline->claimChunk($bucketKey, $limit);
        $rawItems = $claimed['items'];

        if ($rawItems === []) {
            $this->pipeline->finalizeBucket($bucketKey, $registryKey);

            return 0;
        }

        $parsed = BucketKey::parse($bucketKey);

        if ($parsed === null) {
            $this->pipeline->restore($bucketKey, $rawItems, $registryKey);

            return 0;
        }

        $decoded = [];

        foreach ($rawItems as $item) {
            $row = json_decode((string) $item, true);

            if (!is_array($row)) {
                continue;
            }

            $decoded[] = $row;
        }

        if ($decoded === []) {
            $this->pipeline->finalizeBucket($bucketKey, $registryKey);

            return 0;
        }

        $connection = $decoded[0]['db_connection'] ?? $parsed['connection'];
        $table = $decoded[0]['table'] ?? $parsed['table'];
        $type = $decoded[0]['type'] ?? $parsed['type'];

        try {
            $processed = DB::connection($connection)->transaction(function () use ($connection, $table, $type, $decoded, $config): int {
                if ($type === 'insert') {
                    return $this->insertBatcher->execute($connection, $table, $decoded, (int) ($config['max_size'] ?? 1000));
                }

                if ($type === 'update') {
                    return $this->updateBatcher->execute($connection, $table, $decoded);
                }

                return 0;
            });
        } catch (Throwable $e) {
            $this->pipeline->restore($bucketKey, $rawItems, $registryKey);

            throw $e;
        }

        if ($claimed['remaining'] > 0) {
            $this->dispatchFollowUp($bucketKey, $config);
        } else {
            $this->pipeline->finalizeBucket($bucketKey, $registryKey);
        }

        return $processed;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function dispatchFollowUp(string $bucketKey, array $config): void
    {
        if (($config['processor'] ?? 'job') !== 'job') {
            return;
        }

        FlushBatchBucket::dispatch($bucketKey)
            ->onConnection($config['flush_queue_connection'] ?? config('queue.default'))
            ->onQueue($config['flush_queue'] ?? 'sql');
    }
}
