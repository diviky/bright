<?php

namespace Karla\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class CredentialsGuard implements Guard
{
    use GuardHelpers;

    protected $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request  = $request;
    }

    public function user()
    {
        if (!\is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $credentials = $this->getCredentials();

        $user = $this->provider->retrieveByCredentials($credentials);

        if (\is_null($user)) {
            return;
        }

        if (1 != $user->status) {
            return;
        }

        $user->auth_token = $user->access_token;

        return $this->user = $user;
    }

    public function getCredentials()
    {
        $credentials = ['username', 'password'];
        $return      = [];
        foreach ($credentials as $key) {
            $token = $this->request->query($key);
            if (empty($token)) {
                $token = $this->request->input($key);
            }
            $return[$key] = $token;
        }

        return $return;
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
