<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Account;

use Diviky\Bright\Models\Models;
use Diviky\Bright\Routing\Controller as BaseController;

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
     * @param  int  $user_id
     * @return array
     */
    public function person($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        $row = Models::user()::findOrFail($user_id);

        return [
            'user' => $row,
        ];
    }
}
