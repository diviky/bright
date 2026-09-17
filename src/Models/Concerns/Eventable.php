<?php

declare(strict_types=1);

namespace Diviky\Bright\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait Eventable
{
    public static function bootEventable(): void
    {
        static::creating(function (Model $model): void {
            // Always apply insert context columns on create; do not inherit query es(false)
            // from unrelated paths (e.g. batch bulk insert on the same model class).
            $query = $model->newQuery()->getQuery()->es(true);

            $attributes = $model->getAttributes();
            $enriched = $query->insertEvent($attributes)[0];

            $model->setRawAttributes(array_merge($attributes, $enriched));
        });
    }
}
