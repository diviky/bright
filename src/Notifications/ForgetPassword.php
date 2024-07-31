<?php

declare(strict_types=1);

namespace Diviky\Bright\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * @SuppressWarnings(PHPMD)
 */
class ForgetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Token.
     *
     * @var int
     */
    public $token;

    /**
     * Channels to send notifcation.
     *
     * @var array
     */
    public $channels = [];

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $token
     * @param  mixed  $channels
     */
    public function __construct($token, $channels = [])
    {
        $this->token = $token;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (\count($this->channels) > 0) {
            return \array_merge(['database'], $this->channels);
        }

        $channels = config('bright.notifications');

        if (\count($channels) > 0) {
            return \array_merge(['database'], $channels);
        }

        return \array_merge(['database', 'mail'], $this->channels);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Password change request from ' . config('app.name'))
            ->markdown('bright::emails.auth.password', [
                'token' => $this->token,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }
}
