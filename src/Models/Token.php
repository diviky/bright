<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;

class Token extends Model
{
    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'user_id',
        'name',
        'access_token',
        'refresh_token',
        'allowed_ip',
        'expires_at',
        'status',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * {@inheritDoc}
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.tokens', 'tokens');
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     *
     * @return null|static
     */
    public static function findToken($token)
    {
        if (false === strpos($token, '|')) {
            if (strlen($token) > 40) {
                $token = hash('sha256', $token);
            }

            return static::remember(null, 'token:' . $token)
                ->where('access_token', $token)
                ->first();
        }

        [$id, $token] = explode('|', $token, 2);

        $instance = static::remember(null, 'token:' . $token)->find($id);

        if ($instance) {
            return hash_equals($instance->access_token, hash('sha256', $token)) ? $instance : null;
        }
    }

    /**
     * Determine if the token has a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function can($ability)
    {
        return in_array('*', $this->abilities)
               || array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function cant($ability)
    {
        return !$this->can($ability);
    }
}
