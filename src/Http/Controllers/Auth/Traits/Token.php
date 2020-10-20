<?php

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Diviky\Bright\Models\Activation;

trait Token
{
    protected $tokenId;

    public function generateToken()
    {
        return \sprintf('%06d', \mt_rand(1, 999999));
    }

    public function saveToken($user)
    {
        $token = $this->generateToken();

        $activation          = new Activation();
        $activation->user_id = $user->id;
        $activation->token   = $token;
        $activation->save();

        $this->tokenId = $activation->id;

        return $token;
    }

    /**
     * Get the value of tokenId.
     */
    public function getTokenId()
    {
        return $this->tokenId;
    }
}
