<?php

namespace Diviky\Bright\Http\Controllers\Account;

use App\Http\Controllers\Controller as BaseController;
use Diviky\Bright\Models\Models;

class Api extends BaseController
{
    /**
     * @SuppressWarnings(PHPMD)
     */
    public function me(): array
    {
        return [
            'user' => user(),
        ];
    }

    /**
     * Get the user details.
     *
     * @param int $user_id
     *
     * @return array
     */
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
