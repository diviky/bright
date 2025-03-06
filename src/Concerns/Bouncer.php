<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Support\Str;
use Silber\Bouncer\Database\HasRolesAndAbilities;

trait Bouncer
{
    use Authorize;
    use HasRolesAndAbilities;

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
}
