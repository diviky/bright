<?php

namespace Karla\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class AccessTokenGuard implements Guard
{
    use GuardHelpers;

    protected $inputKey   = '';
    protected $storageKey = '';
    protected $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request  = $request;
        // key to check in request
        $this->inputKey = ['access_token', 'token'];
        // key to check in database
        $this->storageKey = 'access_token';
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        // retrieve via token
        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            // the token was found, how you want to pass?
            $user = $this->provider->retrieveByToken($this->storageKey, $token);
        }

        if (is_null($user)) {
            return;
        }

        $allowed_ips = $user->allowed_ip;

        if (!empty($allowed_ips)) {
            $ips         = $this->request->ips();
            $allowed_ips = explode(',', $allowed_ips);
            $allowed     = false;

            foreach ($ips as $ip) {
                foreach ($allowed_ips as $address) {
                    if (IpUtils::checkIp($ip, $address)) {
                        $allowed = true;
                        break 2;
                    }
                }
            }

            if (!$allowed) {
                abort(403, 'Ip Not allowed');

                return;
            }
        }

        return $this->user = $user->user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        foreach ($this->inputKey as $key) {
            $token = $this->request->query($key);
            if (empty($token)) {
                $token = $this->request->input($key);
            }

            if ($token) {
                return $token;
            }
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }
}
