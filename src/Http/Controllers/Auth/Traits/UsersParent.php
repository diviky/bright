<?php

namespace Diviky\Bright\Http\Controllers\Auth\Traits;

use Diviky\Bright\Models\Models;

trait UsersParent
{
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

    public function getUserParent()
    {
        return Models::users()::where('user_id', $this->id)->parent;
    }
}
