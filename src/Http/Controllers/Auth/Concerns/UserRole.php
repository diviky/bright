<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

trait UserRole
{
    /**
     * Assign a role to user.
     *
     * @param  string  $role
     */
    public function assignOwnRole($role): bool
    {
        $this->role = $role;

        return $this->save();
    }

    /**
     * Remove role from user.
     */
    public function removeOwnRole(): bool
    {
        $this->role = null;

        return $this->save();
    }
}
