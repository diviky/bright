<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasAbilities;

class Token extends Model implements HasAbilities
{
    protected $fillable = [
        'user_id',
        'name',
        'access_token',
        'refresh_token',
        'allowed_ip',
        'expires_at',
        'abilities',
        'status',
        'token',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'token',
    ];

    public function getTable()
    {
        return config('bright.table.tokens', 'tokens');
    }

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
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
