<?php

namespace Diviky\Bright\Listeners;

use Diviky\Bright\Mail\Mailable;
use Diviky\Bright\Models\Models;
use Diviky\Bright\Traits\CapsuleManager;
use Illuminate\Auth\Events\PasswordReset as LaravelPasswordReset;
use Illuminate\Http\Request;

class PasswordReset
{
    /**
     * Handle the event.
     */
    public function handle(LaravelPasswordReset $event)
    {
        $user = $event->user;

        $values = [
            'password' => $event->password,
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
