<?php

namespace Karla\Services\Auth;

class AuthTokenGuard extends AccessTokenGuard
{
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

        if ($user->status != 1) {
            return;
        }

        return $this->user = $user;
    }
}
