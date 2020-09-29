<?php

namespace Karla\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class SuccessLogin
{
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
     * @param Login $event
     */
    public function handle(Login $event)
    {
        $user                = $event->user;
        $user->last_login_at = carbon();
        $user->last_login_ip = $this->request->ip();
        $user->save();
    }
}
