<?php

namespace Karla\Http\Controllers\Auth\Traits;

use Karla\Models\Models;

trait UsersParent
{
    public function assignParent($parent_id)
    {
        $values = [
            'parent_id' => $parent_id,
            'user_id'   => $this->id,
        ];

        return Models::users()::create($values);
    }

    public function removeParent()
    {
        return Models::users()::where('user_id', $this->id)
            ->delete();
    }

    public function getParent()
    {
        return Models::users()::where('user_id', $this->id)->parent;
    }
}
