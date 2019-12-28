<?php

namespace Karla\Services\Auth\Providers;

use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;

class AuthTokenProvider implements UserProvider
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function retrieveById($identifier)
    {
        return $this->user
            ->remeber(null, 'uid:' . $identifier)
            ->find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        $user = $this->user->where($identifier, $token)
            ->remember(null, 'access_token:' . $token)
            ->first();

        return $user ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // update via remember token not necessary
    }

    public function retrieveByCredentials(array $credentials)
    {
        // let's try to assume that the credentials ['username', 'password'] given
        $user = $this->user;
        foreach ($credentials as $credentialKey => $credentialValue) {
            if (!Str::contains($credentialKey, 'password')) {
                $user->where($credentialKey, $credentialValue);
            }
        }

        return $user->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return app('hash')->check($plain, $user->getAuthPassword());
    }
}
