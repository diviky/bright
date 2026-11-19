<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

use Illuminate\Support\Facades\Redis;

class RedisPipeline
{
    public function __construct(
        protected string $redisConnection = 'default'
    ) {}

    public static function fromConfig(): self
    {
        $config = config('bright.async.batch', []);

        return new self($config['redis_connection'] ?? 'default');
    }

    public function push(string $bucketKey, string $payload, string $registryKey): int
    {
        $results = Redis::connection($this->redisConnection)->pipeline(function ($pipe) use ($bucketKey, $payload, $registryKey): void {
            $pipe->rpush($bucketKey, $payload);
            $pipe->sadd($registryKey, $bucketKey);
            $pipe->llen($bucketKey);
        });

        return (int) ($results[2] ?? 0);
    }

    /**
     * @return array{items: array<int, string>, remaining: int}
     */
    public function claimChunk(string $bucketKey, int $limit): array
    {
        $results = Redis::connection($this->redisConnection)->transaction(function ($tx) use ($bucketKey, $limit): void {
            $tx->lrange($bucketKey, 0, $limit - 1);
            $tx->ltrim($bucketKey, $limit, -1);
            $tx->llen($bucketKey);
        });

        return [
            'items' => (array) ($results[0] ?? []),
            'remaining' => (int) ($results[2] ?? 0),
        ];
    }

    /**
     * @param  array<int, string>  $payloads
     */
    public function restore(string $bucketKey, array $payloads, string $registryKey): void
    {
        if ($payloads === []) {
            return;
        }

        Redis::connection($this->redisConnection)->pipeline(function ($pipe) use ($bucketKey, $payloads, $registryKey): void {
            foreach ($payloads as $payload) {
                $pipe->rpush($bucketKey, $payload);
            }
            $pipe->sadd($registryKey, $bucketKey);
        });
    }

    public function finalizeBucket(string $bucketKey, string $registryKey): void
    {
        $length = (int) Redis::connection($this->redisConnection)->llen($bucketKey);

        if ($length === 0) {
            Redis::connection($this->redisConnection)->srem($registryKey, $bucketKey);
        }
    }

    public function length(string $bucketKey): int
    {
        return (int) Redis::connection($this->redisConnection)->llen($bucketKey);
    }

    public function headQueuedAt(string $bucketKey): ?int
    {
        $first = Redis::connection($this->redisConnection)->lindex($bucketKey, 0);

        if ($first === null || $first === false || $first === '') {
            return null;
        }

        $decoded = json_decode((string) $first, true);

        if (!is_array($decoded)) {
            return null;
        }

        return isset($decoded['queued_at']) ? (int) $decoded['queued_at'] : null;
    }

    /**
     * @return array<int, string>
     */
    public function registryMembers(string $registryKey): array
    {
        $members = Redis::connection($this->redisConnection)->smembers($registryKey);

        return is_array($members) ? $members : [];
    }
}
