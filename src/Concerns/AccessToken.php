<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

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
     * @param  string  $value
     */
    public function setAccessToken($value = null): void
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
