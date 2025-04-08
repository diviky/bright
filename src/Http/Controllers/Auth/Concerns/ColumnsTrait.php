<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

trait ColumnsTrait
{
    /**
     * Check the column for unique registration.
     *
     * @return string
     */
    protected function username()
    {
        return config('auth.columns.username', 'username');
    }

    /**
     * Return the column to check for password resends.
     *
     * @return string
     */
    protected function address()
    {
        return config('auth.columns.address', 'mobile');
    }
}
