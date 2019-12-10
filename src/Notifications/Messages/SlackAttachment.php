<?php

namespace Karla\Notifications\Messages;

use Karla\Notifications\Messages\SlackAttachmentAction;
use Illuminate\Notifications\Messages\SlackAttachment as BaseSlackAttachment;

class SlackAttachment extends BaseSlackAttachment
{
    public $actions = [];
    public $callback_id;

    public function callbackId($callback_id)
    {
        $this->callback_id = $callback_id;

        return $this;
    }

    /**
     * Add a field to the attachment.
     *
     * @param  \Closure|string $title
     * @param  string $content
     * @return $this
     */
    public function actions($title, $content = '')
    {
        if (is_callable($title)) {
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
