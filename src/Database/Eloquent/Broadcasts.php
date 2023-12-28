<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Illuminate\Database\Eloquent\BroadcastsEvents;

trait Broadcasts
{
    use BroadcastsEvents {
        broadcastConnection as basebroadcastConnection;
        broadcastQueue as basebroadcastQueue;
        broadcastOn as basebroadcastOn;
    }

    public function broadcastAlias(): string
    {
        return '';
    }

    /**
     * The event's broadcast name.
     *
     * @param  string  $event
     */
    public function broadcastAs($event): string
    {
        return strtolower('model.' . $event . '.' . $this->broadcastAlias() . class_basename($this));
    }

    /**
     * Get the data to broadcast for the model.
     *
     * @param  string  $event
     * @return array
     */
    public function broadcastWith($event)
    {
        return [
            'event' => $event,
            'model' => $this->toArray(),
        ];
    }

    /**
     * Get the queue connection that should be used to broadcast model events.
     *
     * @return null|string
     */
    public function broadcastConnection()
    {
        return config($this->broadcastAlias() . 'queue_connection', 'sync');
    }

    /**
     * Get the queue that should be used to broadcast model events.
     *
     * @return null|string
     */
    public function broadcastQueue()
    {
        return config($this->broadcastAlias() . 'perform_on_queue.events', 'default');
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn($event)
    {
        return match ($event) {
            'created' => [$this->broadcastAs($event)],
            default => [$this->broadcastAs($event)],
        };
    }

    /**
     * Create a new broadcastable model event for the model.
     *
     * @param  string  $event
     * @return BroadcastableModelEventOccurred
     */
    protected function newBroadcastableEvent($event)
    {
        return new BroadcastableModelEventOccurred($this, $event);
    }
}
