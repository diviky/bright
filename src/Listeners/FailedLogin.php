<?php

namespace Karla\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Karla\Traits\CapsuleManager;

class FailedLogin
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
     * @param Failed $event
     */
    public function handle(Failed $event)
    {
        $user = $event->user;

        $this->db->table('auth_login_history')->insert([
            'id'         => Str::uuid(),
            'user_id'    => is_null($user) ? null : $user->id,
            'ip'         => $this->request->ip(),
            'ips'        => implode(',', $this->request->getClientIps()),
            'host'       => $this->request->getHost(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => carbon(),
            'meta'       => json_encode($event->credentials),
            'status'     => 2,
        ]);
    }
}
