<?php

namespace Karla\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Mobtexting\MobtextingChannel;
use NotificationChannels\Mobtexting\MobtextingSmsMessage;

class ForgetPassword extends Notification
{
    use Queueable;

    protected $token;

    protected $channels = [];

    /**
     * Create a new notification instance.
     *
     * @param mixed $token
     * @param mixed $channels
     */
    public function __construct($token, $channels = [])
    {
        $this->token    = $token;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        if (\count($this->channels) > 0) {
            return \array_merge(['database'], $this->channels);
        }

        $channels = config('karla.notifications');

        if (\count($channels) > 0) {
            return \array_merge(['database'], $channels);
        }

        return \array_merge(['database', 'mail', MobtextingChannel::class], $this->channels);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Password change request from ' . config('app.name'))
            ->markdown('karla::emails.auth.password', [
                'token'      => $this->token,
                'notifiable' => $notifiable,
            ]);
    }

    public function toMobtexting($notifiable)
    {
        return (new MobtextingSmsMessage())
            ->text('Your Account OTP is ' . $this->token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }
}
