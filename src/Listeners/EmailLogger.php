<?php

declare(strict_types=1);

namespace Diviky\Bright\Listeners;

use Diviky\Bright\Models\Models;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class EmailLogger
{
    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        $message = $event->message;

        Models::emailLogs()::create([
            'id' => Str::uuid(),
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'cc' => $this->formatAddressField($message, 'Cc'),
            'bcc' => $this->formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getHtmlBody(),
            'headers' => $message->getHeaders()->toString(),
            'attachments' => $this->saveAttachments($message),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     */
    public function formatAddressField(Email $message, string $field): ?string
    {
        $headers = $message->getHeaders();

        return $headers->get($field)?->getBodyAsString();
    }

    /**
     * Collect all attachments and format them as strings.
     */
    protected function saveAttachments(Email $message): ?string
    {
        if (empty($message->getAttachments())) {
            return null;
        }

        return collect($message->getAttachments())
            ->map(fn (DataPart $part) => $part->toString())
            ->implode("\n\n");
    }
}
