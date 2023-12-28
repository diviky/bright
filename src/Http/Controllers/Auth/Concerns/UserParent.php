<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

use App\Models\User;
use Diviky\Bright\Models\Models;

trait UserParent
{
    /**
     * Add parent id.
     *
     * @param  null|int  $parent_id
     */
    public function assignParent($parent_id = null): bool
    {
        if ($parent_id === null) {
            if (app()->has('owner')) {
                $parent_id = app()->get('owner');
            } else {
                $parent_id = User::where('role', $this->admin)
                    ->orderBy('id', 'asc')
                    ->take(1)
                    ->value('id');
            }
        }

        $this->parent_id = $parent_id;

        return $this->save();
    }

    /**
     * Remove parent id.
     */
    public function removeParent(): bool
    {
        $this->parent_id = null;

        return $this->save();
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Models::user(), 'parent_id');
    }
}
