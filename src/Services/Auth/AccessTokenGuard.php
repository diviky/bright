<?php

declare(strict_types=1);

namespace Diviky\Bright\Services\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class AccessTokenGuard
{
    use GuardHelpers;

    /**
     * Request keys to check.
     *
     * @var array
     */
    protected $inputKeys = [];

    /**
     * database column.
     *
     * @var string
     */
    protected $storageKey = '';

    /**
     * database column.
     *
     * @var string
     */
    protected $inputKey = '';

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
        $this->config = $config;
        $this->provider = $provider;
        $this->request = $request;
        // key to check in request
        $this->inputKeys = ['access_token', 'api_token'];
        // key to check in database
        $this->storageKey = 'access_token';
        $this->inputKey = 'access_token';
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

        $access_key = $this->getTokenForRequest();

        if (empty($access_key)) {
            return null;
        }

        $token = null;
        $signature = null;
        if (\strpos($access_key, ':') !== false) {
            [$access_key, $signature] = \explode(':', $access_key, 2);
        }

        // the token was found, how do you want to pass?
        $token = $this->provider->retrieveByToken($this->storageKey, $access_key);

        if (\is_null($token) || \is_null($token->user_id)) {
            return null;
        }

        if ($token->status != 1 || !\is_null($token->deleted_at)) {
            return null;
        }

        if (!$this->validateIp($token->allowed_ip)) {
            return null;
        }

        if (isset($token->expires_at) && $token->expires_at->isPast()) {
            return null;
        }

        if (!empty($token->refresh_token) && !$this->validateSignature($token, $signature)) {
            return null;
        }

        $user = $this->provider->retrieveById($token->user_id);

        if (is_null($user) || !\is_null($user->deleted_at) || $user->status != 1) {
            return null;
        }

        $this->user = $user;
        $this->user->token = $token;

        return $this->user;
    }

    /**
     * Get the token for the current request.
     *
     * @return null|array|string
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
                    $token = $this->request->post($key);
                }

                if ($token) {
                    return $token;
                }
            }
        }

        if (empty($token)) {
            $token = md5($this->request->ip());
        }

        return $token;
    }

    /**
     * Validate ip adress with given list.
     *
     * @param  null|string  $allowed_ips
     */
    protected function validateIp($allowed_ips = null): bool
    {
        if (empty($allowed_ips)) {
            return true;
        }

        $ips = $this->request->ips();
        $allowed_ips = \explode(',', $allowed_ips);
        $allowed = false;

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
     * @param  object  $token
     * @param  null|string  $signature
     */
    protected function validateSignature($token, $signature = null): bool
    {
        $algo = $this->request->header('X-Auth-Method', 'SHA256');
        $nonce = $this->request->header('X-Auth-Nonce');
        $date = $this->request->header('X-Auth-Date');
        $date = $date ?? $this->request->header('Date');
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
