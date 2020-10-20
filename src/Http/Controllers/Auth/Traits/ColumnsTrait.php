<?php

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

trait ColumnsTrait
{
    protected function username()
    {
        return config('auth.columns.username', 'username');
    }

    protected function address()
    {
        return config('auth.columns.address', 'mobile');
    }
}
