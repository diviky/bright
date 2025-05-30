<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Model;

trait Nanoids
{
    protected int $nanoidSize = 18;

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Boot function from Laravel.
     */
    public static function bootNanoids(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (new Client)->generateId(size: $this->nanoidSize);
            }
        });
    }
}
