<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
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
        'refresh_token',
        'token',
    ];

    public function getTable()
    {
        return config('bright.table.tokens', 'tokens');
    }

    protected static function boot()
    {

        self::creating(function ($model) {
            $model->access_token = $model->access_token ?? $model->token;

            return $model;
        });

        parent::boot();
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param  string  $token
     * @return null|static
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
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
}
