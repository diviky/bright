<?php

namespace Diviky\Bright\Http\Controllers\Account;

use App\Http\Controllers\Controller as BaseController;
use Diviky\Bright\Models\Models;

class Api extends BaseController
{
    public function me()
    {
        return [
            'user' => user(),
        ];
    }

    public function person($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        $row = Models::user()::where('id', $user_id)
            ->first();

        return [
            'user' => $row,
        ];
    }
}
