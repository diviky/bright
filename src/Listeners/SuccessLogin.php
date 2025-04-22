<?php

declare(strict_types=1);

namespace Diviky\Bright\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class SuccessLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $user->last_login_at = now();
        $user->last_login_ip = $this->request->ip();
        $user->save();
    }
}
