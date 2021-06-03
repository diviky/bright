<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth\Providers;

use App\Models\User;
use Diviky\Bright\Models\Token;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;

class AccessProvider implements UserProvider
{
    protected $token;

    protected $user;

    public function __construct(User $user, Token $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveById($identifier)
    {
        return $this->user
            ->remember(null, 'uid:' . $identifier)
            ->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param string $token
     *
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    public function retrieveByAccessToken($token)
    {
        return $this->user->where($this->user->getAccessTokenName(), $token)
            ->remember(null, 'access_token:' . $token)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->token
            ->remember(null, 'token:' . $token)
            ->where($identifier, $token)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // update via remember token not necessary
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveByCredentials(array $credentials)
    {
        $key = null;
        // let's try to assume that the credentials ['username', 'password'] given
        $user = $this->user;
        foreach ($credentials as $credentialKey => $credentialValue) {
            if (!Str::contains($credentialKey, 'password')) {
                $user = $user->where($credentialKey, $credentialValue);
                $key .= $credentialKey . ':' . $credentialValue;
            }
        }

        if ($key) {
            $user = $user->remember(null, 'cre:' . $key);
        }

        return $user->first();
    }

    /**
     * {@inheritDoc}
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return app('hash')->check($plain, $user->getAuthPassword());
    }
}
