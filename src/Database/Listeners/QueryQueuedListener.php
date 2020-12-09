<?php

namespace Diviky\Bright\Database\Listeners;

use Diviky\Bright\Database\Jobs\Statement;
use Diviky\Bright\Database\Events\QueryQueued as QueryQueuedEvent;

class QueryQueuedListener
{
    /**
     * Handle the event.
     */
    public function handle(QueryQueuedEvent $event)
    {
        $async = $event->async;

        Statement::dispatch($event->sql, $event->bindings)
            ->onConnection($async[0])
            ->onQueue($async[1]);
    }
}
