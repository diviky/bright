<?php

namespace Karla\Http\Controllers\Account;

use App\Http\Controllers\Controller as BaseController;

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

        $row = $this->db->table('auth_users')
            ->where('id', $user_id)
            ->first();

        return [
            'user' => $row,
        ];

    }
}
