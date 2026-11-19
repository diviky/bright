<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

use Diviky\Bright\Database\Jobs\FlushBatchBucket;

class RedisBatchBuffer
{
    public function __construct(
        protected RedisPipeline $pipeline
    ) {}

    /**
     * @param  array<int|string, mixed>  $recordPayload
     */
    public function pushRow(string $table, string $type, array $recordPayload, string $dbConnection, ?array $async = null): bool
    {
        $config = config('bright.async.batch', []);
        $prefix = (string) ($config['key_prefix'] ?? 'bright:batch');
        $registryKey = (string) ($config['registry_key'] ?? 'bright:batch:registry');
        $maxSize = (int) ($config['max_size'] ?? 1000);
        $maxWait = (int) ($config['max_wait_seconds'] ?? 5);

        $bucketKey = BucketKey::make($prefix, $dbConnection, $table, $type);

        $payload = json_encode(array_merge($recordPayload, [
            'table' => $table,
            'type' => $type,
            'db_connection' => $dbConnection,
            'queued_at' => time(),
        ]));

        if ($payload === false) {
            return false;
        }

        $length = $this->pipeline->push($bucketKey, $payload, $registryKey);

        if (($config['processor'] ?? 'job') === 'job') {
            $queueConnection = $config['flush_queue_connection'] ?? config('queue.default');
            $queue = $config['flush_queue'] ?? 'sql';

            if ($length >= $maxSize) {
                FlushBatchBucket::dispatch($bucketKey)
                    ->onConnection($queueConnection)
                    ->onQueue($queue);
            } elseif ($length === 1) {
                FlushBatchBucket::dispatch($bucketKey)
                    ->onConnection($queueConnection)
                    ->onQueue($queue)
                    ->delay(now()->addSeconds($maxWait));
            }
        }

        unset($async);

        return true;
    }
}
