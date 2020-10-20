<?php

namespace Diviky\Bright\Notifications\Messages;

use Closure;
use Illuminate\Notifications\Messages\SlackMessage as BaseSlackMessage;

class SlackMessage extends BaseSlackMessage
{
    /**
     * Define an attachment for the message.
     *
     * @return $this
     */
    public function attachment(Closure $callback)
    {
        $this->attachments[] = $attachment = new SlackAttachment();

        $callback($attachment);

        return $this;
    }
}
