<?php

declare(strict_types=1);

namespace Diviky\Bright\Listeners;

use Diviky\Bright\Mail\Mailable;
use Diviky\Bright\Models\Models;
use Illuminate\Auth\Events\PasswordReset as LaravelPasswordReset;

class PasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(LaravelPasswordReset $event): void
    {
        $user = $event->user;

        $values = [
            'password' => $event->password ?? null,
        ];

        $save               = [];
        $save['user_id']    = $user->id;
        $save['created_at'] = carbon();
        $save['password']   = $user->password;

        Models::passwordHistory()::create($save);

        (new Mailable())
            ->subject('Your password has been changed!!!')
            ->with([
                'row'  => $values,
                'user' => $user,
            ])
            ->prefix('bright::emails.')
            ->markdown('auth.password_changed')
            ->deliver($user);
    }
}
