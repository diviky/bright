<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class CredentialsGuard implements Guard
{
    use GuardHelpers;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Guard configuration.
     *
     * @var array
     */
    protected $config = [];

    public function __construct(UserProvider $provider, Request $request, array $config = [])
    {
        $this->config = $config;
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function user()
    {
        $user = null;

        $credentials = $this->getCredentials();

        $user = $this->provider->retrieveByCredentials($credentials);

        if (\is_null($user)) {
            return;
        }

        if (!\is_null($user->deleted_at)) {
            return;
        }

        if (1 != $user->status) {
            return;
        }

        $valid = $this->provider->validateCredentials($user, $credentials);

        if (!$valid) {
            return;
        }

        return $this->user = $user;
    }

    /**
     * @psalm-return array<array-key|mixed, mixed>
     */
    public function getCredentials(): array
    {
        $credentials = [$this->username(), 'password'];
        $return = [];
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

    /**
     * Get the user identifier.
     *
     * @return string
     */
    protected function username()
    {
        return config('auth.columns.username', 'username');
    }
}
