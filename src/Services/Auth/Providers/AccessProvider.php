<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth\Providers;

use App\Models\User;
use Diviky\Bright\Models\Token;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccessProvider implements UserProvider
{
    protected $token;

    protected $user;

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    public function __construct(User $user, Token $token, HasherContract $hasher)
    {
        $this->user = $user;
        $this->token = $token;
        $this->hasher = $hasher;

    }

    public function retrieveById($identifier)
    {
        return $this->user
            ->remember(null, 'uid:' . $identifier)
            ->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  string  $token
     * @return null|\Illuminate\Contracts\Auth\Authenticatable
     */
    public function retrieveByAccessToken($token)
    {
        return $this->user->where($this->user->getAccessTokenName(), $token)
            ->remember(null, 'access_token:' . $token)
            ->first();
    }

    public function retrieveByToken($identifier, $token)
    {
        if (strpos($token, '|') === false) {
            if (strlen($token) > 40) {
                $token = hash('sha256', $token);
            }

            return $this->token
                ->remember(null, 'token:' . $token)
                ->where($identifier, $token)
                ->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if (!empty($id)) {
            $id = Arr::last(explode(' ', $id));
        }

        $instance = $this->token->remember(null, 'token:' . $id . $token)->find($id);

        if ($instance) {
            return hash_equals($instance->{$identifier}, hash('sha256', $token)) ? $instance : null;
        }

        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // update via remember token not necessary
    }

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

        if (isset($key)) {
            $user = $user->remember(null, 'cre:' . $key);
        }

        return $user->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if enabled and required.
     *
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, $force = false)
    {
        if (!$this->hasher->needsRehash($user->getAuthPassword()) && !$force) {
            return;
        }

        $user->forceFill([
            $user->getAuthPasswordName() => $this->hasher->make($credentials['password']),
        ])->save();
    }
}
