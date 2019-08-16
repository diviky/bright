<?php

namespace Karla\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Karla\Listeners\Traits\Device;
use Karla\Traits\CapsuleManager;

class SuccessLogin
{
    use CapsuleManager;
    use Device;

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

        $ip         = $this->request->ip();
        $user_agent = $this->request->userAgent();

        $values = [
            'id'         => Str::uuid(),
            'user_id'    => $user->id,
            'ip'         => $ip,
            'ips'        => \implode(',', $this->request->getClientIps()),
            'host'       => $this->request->getHost(),
            'user_agent' => $user_agent,
            'created_at' => carbon(),
            'status'     => 1,
        ];

        $values = \array_merge($values, $this->getDeviceDetails($ip, $user_agent));

        $this->db->table('auth_login_history')->insert($values);
    }
}
