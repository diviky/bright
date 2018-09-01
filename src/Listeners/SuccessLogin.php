<?php

namespace Karla\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Karla\Traits\CapsuleManager;

class SuccessLogin
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
     * @param Login $event
     */
    public function handle(Login $event)
    {
        $user                = $event->user;
        $user->last_login_at = date('Y-m-d H:i:s');
        $user->last_login_ip = $this->request->ip();
        $user->save();

        $this->db->table('auth_login_history')->insert([
            'id'         => Str::uuid(),
            'user_id'    => $user->id,
            'ip'         => $this->request->ip(),
            'ips'        => implode(',', $this->request->getClientIps()),
            'host'       => $this->request->getHost(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => carbon(),
        ]);
    }
}
