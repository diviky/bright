<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class AccessTokenGuard implements Guard
{
    use GuardHelpers;

    /**
     * Request keys to check.
     *
     * @var array
     */
    protected $inputKeys  = [];

    /**
     * database column.
     *
     * @var string
     */
    protected $storageKey = '';

    /**
     * Guard configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var Request
     */
    protected $request;

    public function __construct(UserProvider $provider, Request $request, array $config = [])
    {
        $this->config   = $config;
        $this->provider = $provider;
        $this->request  = $request;
        // key to check in request
        $this->inputKeys = ['access_token', 'api_token'];
        // key to check in database
        $this->storageKey = 'access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function user()
    {
        $token      = null;
        $signature  = null;
        $access_key = $this->getTokenForRequest();

        if (!empty($access_key)) {
            if (false !== \strpos($access_key, ':')) {
                list($access_key, $signature) = \explode(':', $access_key, 2);
            }

            // the token was found, how you want to pass?
            $token = $this->provider->retrieveByToken($this->storageKey, $access_key);
        }

        if (\is_null($token) || \is_null($token->user_id)) {
            return;
        }

        if (1 != $token->status || !\is_null($token->deleted_at)) {
            return;
        }

        $allowed_ips = $token->allowed_ip;
        $allowed     = $this->validateIp($allowed_ips);

        if (!$allowed) {
            return null;
        }

        if (!empty($token->refresh_token) && !$this->validateSignature($token, $signature)) {
            return null;
        }

        $user = $this->provider->retrieveById($token->user_id);

        if (is_null($user) || !\is_null($user->deleted_at) || 1 != $user->status) {
            return null;
        }

        $this->user = $user;

        return $this->user;
    }

    /**
     * Get the token for the current request.
     *
     * @return mixed
     */
    public function getTokenForRequest()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            $token = $this->request->header('Authorization');
        }

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        if (empty($token)) {
            foreach ($this->inputKeys as $key) {
                $token = $this->request->query($key);
                if (empty($token)) {
                    $token = $this->request->input($key);
                }

                if ($token) {
                    return $token;
                }
            }
        }

        return $token;
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
     * Validate ip adress with given list.
     *
     * @param null|string $allowed_ips
     */
    protected function validateIp($allowed_ips = null): bool
    {
        if (empty($allowed_ips)) {
            return true;
        }

        $ips         = $this->request->ips();
        $allowed_ips = \explode(',', $allowed_ips);
        $allowed     = false;

        foreach ($ips as $ip) {
            foreach ($allowed_ips as $address) {
                if (IpUtils::checkIp($ip, \trim($address))) {
                    $allowed = true;

                    break 2;
                }
            }
        }

        if (!$allowed) {
            return false;
        }

        return true;
    }

    /**
     * Validate request signature.
     *
     * @param object      $token
     * @param null|string $signature
     */
    protected function validateSignature($token, $signature = null): bool
    {
        //check is expired
        if (isset($token->expires_in) && now()->gt($token->expires_in)) {
            return false;
        }

        $algo   = $this->request->header('X-Auth-Method', 'SHA256');
        $nonce  = $this->request->header('X-Auth-Nonce');
        $date   = $this->request->header('X-Auth-Date');
        $date   = $date ?? $this->request->header('Date');
        $method = $this->request->method();
        $fields = $this->request->all();
        $fields = \http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

        $sign = [
            $method,
            $algo,
            $date,
            $nonce,
            $fields,
            $token->access_token,
        ];

        $data = \implode("\n", $sign);

        $hmac = \hash_hmac($algo, $data, $token->refresh_token, true);
        $hmac = \base64_encode($hmac);

        return $signature === $hmac;
    }
}
