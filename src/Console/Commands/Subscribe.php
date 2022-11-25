<?php

declare(strict_types=1);

namespace Diviky\Bright\Console\Commands;

use Diviky\Bright\Console\Command;
use Illuminate\Support\Facades\Redis;

class Subscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:subscribe
        {topics* : Topics to Subscribe}
        {--connection : Broadcasting connection}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Subscribe to the database broadcast events';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $topics = $this->argument('topics');
        $connection = $this->option('connection');
        $connection = $connection ?: config('broadcasting.connections.redis.connection');

        if (is_null($connection) || !is_string($connection)) {
            $this->error('connection not found');

            return;
        }

        $topics = is_null($topics) ? ['model.*'] : $topics;

        // general is the name of channel to subscribe to
        Redis::connection($connection)->psubscribe($topics, function (string $message, string $channel): void {
            // message in here is the data strring sent/publish
            $payload = json_decode($message, true);

            $event = $payload['event'] ?? null;

            if ($event && preg_match('/model.([\w]+).(.*)/', $event, $matches)) {
                $action = $matches[1] ?? null;
                if ('created' == $action) {
                    $this->created($event, $payload['model']);
                } elseif ('updated' == $action) {
                    $this->updated($event, $payload['model']);
                } elseif ('deleted' == $action) {
                    $this->deleted($event, $payload['model']);
                } elseif ('trashed' == $action) {
                    $this->trashed($event, $payload['model']);
                }
            }

            unset($channel);
        });
    }

    protected function deleted(string $event, array $payload): bool
    {
        unset($event, $payload);

        return true;
    }

    protected function created(string $event, array $payload): bool
    {
        unset($event, $payload);

        return true;
    }

    protected function updated(string $event, array $payload): bool
    {
        unset($event, $payload);

        return true;
    }

    protected function trashed(string $event, array $payload): bool
    {
        unset($event, $payload);

        return true;
    }
}
