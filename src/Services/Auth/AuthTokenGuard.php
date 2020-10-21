<?php

namespace Diviky\Bright\Services\Auth;

class AuthTokenGuard extends AccessTokenGuard
{
    public function user()
    {
        if (!\is_null($this->user)) {
            return $this->user;
        }

        $user = null;
        // retrieve via token
        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            // the token was found, how you want to pass?
            $user = $this->provider->retrieveByAccessToken($token);
        }

        if (\is_null($user)) {
            return;
        }

        if (!\is_null($user->deleted_at)) {
            return;
        }

        if (1 != $user->status) {
            return;
        }

        return $this->user = $user;
    }
}
