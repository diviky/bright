<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;

class BroadcastableModelEventOccurred implements ShouldBroadcast
{
    use InteractsWithSockets;

    /**
     * The model instance corresponding to the event.
     *
     * @var Model
     */
    public $model;

    /**
     * The queue connection that should be used to queue the broadcast job.
     *
     * @var string
     */
    public $connection;

    /**
     * The queue that should be used to queue the broadcast job.
     *
     * @var string
     */
    public $queue;

    /**
     * The event name (created, updated, etc.).
     *
     * @var string
     */
    protected $event;

    /**
     * The channels that the event should be broadcast on.
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Create a new event instance.
     *
     * @param  Model  $model
     * @param  string  $event
     */
    public function __construct($model, $event)
    {
        $this->model = $model;
        $this->event = $event;
    }

    #[\Override]
    public function broadcastOn()
    {
        $channels = empty($this->channels)
                ? ($this->model->broadcastOn($this->event) ?: [])
                : $this->channels;

        return collect($channels)->map(function ($channel) {
            return $channel instanceof Model ? new PrivateChannel((string) $channel) : $channel;
        })->all();
    }

    /**
     * The name the event should broadcast as.
     *
     * @return string
     */
    public function broadcastAs()
    {
        $default = class_basename($this->model) . ucfirst($this->event);

        return method_exists($this->model, 'broadcastAs')
                ? ($this->model->broadcastAs($this->event) ?: $default)
                : $default;
    }

    /**
     * Get the data that should be sent with the broadcasted event.
     *
     * @return null|array
     */
    public function broadcastWith()
    {
        return method_exists($this->model, 'broadcastWith')
            ? $this->model->broadcastWith($this->event)
            : null;
    }

    /**
     * Manually specify the channels the event should broadcast on.
     *
     * @return $this
     */
    public function onChannels(array $channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * Determine if the event should be broadcast synchronously.
     *
     * @return bool
     */
    public function shouldBroadcastNow()
    {
        return $this->event === 'deleted'
               && !method_exists($this->model, 'bootSoftDeletes');
    }

    /**
     * Get the event name.
     *
     * @return string
     */
    public function event()
    {
        return $this->event;
    }
}
