<?php

namespace Karla\Listeners;

use Illuminate\Auth\Events\PasswordReset as LaravelPasswordReset;
use Illuminate\Http\Request;
use Karla\Mail\Mailable;
use Karla\Models\Models;
use Karla\Traits\CapsuleManager;

class PasswordReset
{
    use CapsuleManager;

    /**
     * Create the event listener.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param LaravelPasswordReset $event
     */
    public function handle(LaravelPasswordReset $event)
    {
        $user     = $event->user;
        $inputpwd = $this->request->input('password');

        $values = [
            'password' => $inputpwd,
        ];

        (new Mailable())
            ->subject('Your password has been changed!!!')
            ->with([
                'row'  => $values,
                'user' => $user,
            ])
            ->prefix('karla::emails.')
            ->markdown('auth.password_changed')
            ->deliver($user);

        $save               = [];
        $save['user_id']    = $user->id;
        $save['created_at'] = carbon();
        $save['password']   = $user->password;

        Models::passwordHistory()::create($save);
    }
}
