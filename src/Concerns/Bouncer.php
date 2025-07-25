<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Bouncer as BouncerFacade;
use Illuminate\Support\Str;
use Silber\Bouncer\Database\HasRolesAndAbilities;

trait Bouncer
{
    use Authorize;
    use HasRolesAndAbilities;

    public static function bootBouncer()
    {
        static::deleting(function (self $model) {
            BouncerFacade::refreshFor($model);
        });

        static::created(function (self $model) {
            BouncerFacade::refreshFor($model);
        });

        static::updated(function (self $model) {
            BouncerFacade::refreshFor($model);
        });
    }

    /**
     * Check user as right permission.
     *
     * @param  string  $ability
     * @return null|mixed
     */
    public function isForbid($ability)
    {
        [$option, $view] = \array_pad(\explode('.', $ability), 2, null);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $ability,
        ];

        $abilities = $this->getForbiddenAbilities();

        foreach ($abilities as $ability) {
            foreach ($matches as $match) {
                if (Str::is($ability->name, $match)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check user as right permission.
     *
     * @param  string  $ability
     * @return null|mixed
     */
    protected function isMatched($ability)
    {
        [$option, $view] = \array_pad(\explode('.', $ability), 2, null);

        $matches = [
            '*',
            $option . '.*',
            $option . '.' . $view,
            $ability,
        ];

        $abilities = $this->getAbilities();

        $granted = null;
        foreach ($abilities as $ability) {
            foreach ($matches as $match) {
                if (Str::is($ability->name, $match)) {
                    $granted = $ability;

                    break 2;
                }
            }
        }

        return $granted;
    }

    public function attachRole($role)
    {
        return $this->assign($role);
    }

    public function assignRole($role)
    {
        return $this->assign($role);
    }

    public function removeRole($role)
    {
        return $this->retract($role);
    }

    /**
     * Check the user has permission.
     *
     * @param  string  $permission
     * @param  null|string  $guardName
     *
     * @SuppressWarnings(PHPMD)
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->isMatched($permission);

        return ($granted) ? true : false;
    }

    /**
     * Get the user first role.
     *
     * @return null|string
     */
    public function getRole()
    {
        return $this->getRoles()->first();
    }

    public function hasAnyRole(...$roles): bool
    {
        if (count($roles) === 1 && is_string($roles[0])) {
            $roles = explode('|', $roles[0]);
        }

        if (is_array($roles[0])) {
            $roles = $roles[0];
        }

        return $this->isAn(...$roles);
    }

    public function hasRole($roles): bool
    {
        return $this->isA($roles);
    }

    public function hasAllRoles(...$roles): bool
    {
        return $this->isAll(...$roles);
    }
}
