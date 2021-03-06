<?php

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Illuminate\Support\Str;

trait AccessToken
{
    /**
     * Get the token value for the "remember me" session.
     *
     * @return null|string
     */
    public function getAccessToken()
    {
        if (!empty($this->getAccessTokenName())) {
            return (string) $this->{$this->getAccessTokenName()};
        }
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     */
    public function setAccessToken($value = null)
    {
        if (!empty($this->getAccessTokenName())) {
            $value = $value ?: Str::random(30);

            $this->{$this->getAccessTokenName()} = $value;
        }
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getAccessTokenName()
    {
        return $this->accessTokenName;
    }
}
