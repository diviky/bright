<?php

namespace Karla\Notifications\Messages;

use Closure;
use Karla\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage as BaseSlackMessage;

class SlackMessage extends BaseSlackMessage
{
    /**
     * Define an attachment for the message.
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function attachment(Closure $callback)
    {
        $this->attachments[] = $attachment = new SlackAttachment;

        $callback($attachment);

        return $this;
    }
}
