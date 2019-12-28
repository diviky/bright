<?php

namespace Karla\Listeners;

use Karla\Mail\Mailable;
use Illuminate\Http\Request;
use Karla\Traits\CapsuleManager;
use Illuminate\Auth\Events\PasswordReset as LaravelPasswordReset;

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

        $this->db->table('auth_password_history')
            ->timestamps(false)
            ->insert($save);
    }
}
