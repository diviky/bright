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
        $this->inputKey = 'token';
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
            return null;
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
                return null;
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
        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
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
