<?php

declare(strict_types=1);

namespace Diviky\Bright\Notifications\Channels;

use Diviky\Bright\Notifications\Messages\SlackAttachmentAction;
use Illuminate\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackChannel extends SlackWebhookChannel
{
    protected function attachments(SlackMessage $message)
    {
        return collect($message->attachments)->map(function ($attachment) use ($message) {
            return \array_filter([
                'author_icon' => $attachment->authorIcon,
                'author_link' => $attachment->authorLink,
                'author_name' => $attachment->authorName,
                'color'       => $attachment->color ?: $message->color(),
                'fallback'    => $attachment->fallback,
                'fields'      => $this->fields($attachment),
                'actions'     => $this->actions($attachment),
                'footer'      => $attachment->footer,
                'callback_id' => $attachment->callback_id,
                'footer_icon' => $attachment->footerIcon,
                'image_url'   => $attachment->imageUrl,
                'mrkdwn_in'   => $attachment->markdown,
                'pretext'     => $attachment->pretext,
                'text'        => $attachment->content,
                'thumb_url'   => $attachment->thumbUrl,
                'title'       => $attachment->title,
                'title_link'  => $attachment->url,
                'ts'          => $attachment->timestamp,
            ]);
        })->all();
    }

    /**
     * Format the attachment's fields.
     *
     * @return array
     */
    protected function actions(SlackAttachment $attachment)
    {
        return collect($attachment->actions)->map(function ($value) {
            if ($value instanceof SlackAttachmentAction) {
                return $value->toArray();
            }

            return $value;
        })->values()->all();
    }
}
