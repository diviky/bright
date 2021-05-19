<?php

namespace Diviky\Bright\Database\Listeners;

use Diviky\Bright\Database\Events\QueryQueued as QueryQueuedEvent;
use Diviky\Bright\Database\Jobs\Statement;

class QueryQueuedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(QueryQueuedEvent $event): void
    {
        $async = $event->async;

        Statement::dispatch($event->sql, $event->bindings)
            ->onConnection($async[0])
            ->onQueue($async[1]);
    }
}
