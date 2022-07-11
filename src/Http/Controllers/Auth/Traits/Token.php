<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Diviky\Bright\Models\Activation;

trait Token
{
    /**
     * Token value.
     *
     * @var int
     */
    protected $tokenId;

    /**
     * Generate random token.
     */
    public function generateToken(): string
    {
        return \sprintf('%06d', \random_int(1, 999999));
    }

    /**
     * Save token to database.
     *
     * @param object $user
     *
     * @return string
     */
    public function saveToken($user)
    {
        $token = $this->generateToken();

        $values = [
            'token' => $token,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(15),
        ];

        $activation = Activation::create($values);

        $this->tokenId = $activation->id;

        return $token;
    }

    /**
     * Get the value of tokenId.
     */
    public function getTokenId(): int
    {
        return $this->tokenId;
    }
}
