<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class CredentialsGuard
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
     * Get the currently authenticated user.
     *
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    public function user()
    {
        if (!\is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $credentials = $this->getCredentials();

        $user = $this->provider->retrieveByCredentials($credentials);

        if (\is_null($user) || !\is_null($user->deleted_at) || $user->status != 1) {
            return null;
        }

        $valid = $this->provider->validateCredentials($user, $credentials);

        if (!$valid) {
            return null;
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
     * Get the user identifier.
     *
     * @return string
     */
    protected function username()
    {
        return config('auth.columns.username', 'username');
    }
}
