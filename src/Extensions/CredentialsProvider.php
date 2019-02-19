<?php

namespace Karla\Extensions;

use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;

class CredentialsProvider implements UserProvider
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
                $user = $user->where($credentialKey, $credentialValue);
            }
        }

        $row = $user->first();

        if (is_null($row)) {
            return;
        }

        $valid = $this->validateCredentials($row, $credentials);

        if (!$valid) {
            return;
        }

        return $row;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return app('hash')->check($plain, $user->getAuthPassword());
    }
}
