<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Diviky\Bright\Models\Models;
use Illuminate\Database\Eloquent\Model;

trait UsersParent
{
    /**
     * Assign user to user.
     *
     * @param int $parent_id
     *
     * @return Model
     */
    public function assignUserParent($parent_id)
    {
        $values = [
            'parent_id' => $parent_id,
            'user_id'   => $this->id,
        ];

        return Models::users()::create($values);
    }

    /**
     * Remove the user parent.
     *
     * @return bool
     */
    public function removeUserParent()
    {
        return Models::users()::where('user_id', $this->id)
            ->delete();
    }

    /**
     * Get the user parent details.
     *
     * @return mixed
     */
    public function getUserParent()
    {
        return Models::users()::where('user_id', $this->id)->parent;
    }
}
