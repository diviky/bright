<?php

declare(strict_types=1);

namespace Diviky\Bright\Notifications\Messages;

use Illuminate\Notifications\Messages\SlackAttachment as BaseSlackAttachment;

class SlackAttachment extends BaseSlackAttachment
{
    /**
     * Slack action.
     *
     * @var array
     */
    public $actions = [];

    /**
     * @var int|string
     */
    public $callback_id;

    /**
     * Set callback id.
     *
     * @param  id|string  $callback_id
     * @return $this
     */
    public function callbackId($callback_id)
    {
        $this->callback_id = $callback_id;

        return $this;
    }

    /**
     * Add a field to the attachment.
     *
     * @param  \Closure|string  $title
     * @param  string  $content
     * @return $this
     */
    public function actions($title, $content = '')
    {
        if (\is_callable($title)) {
            $callback = $title;

            $callback($attachmentAction = new SlackAttachmentAction);

            $this->actions[] = $attachmentAction;

            return $this;
        }

        $this->actions[] = [
            'text' => $content,
            'name' => $title,
            'type' => 'button',
        ];

        return $this;
    }
}
