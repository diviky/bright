<?php

declare(strict_types=1);

namespace Diviky\Bright\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait Eventable
{
    public static function bootEventable(): void
    {
        static::creating(function (Model $model): void {
            $model->setRawAttributes($model->getQuery()->insertEvent($model->getAttributes())[0]);
        });
    }
}
