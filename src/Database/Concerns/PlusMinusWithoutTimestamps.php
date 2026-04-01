<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Contracts\Database\Query\Expression;

trait PlusMinusWithoutTimestamps
{
    /**
     * Increment a column without updating the model's `updated_at` timestamp.
     *
     * @param  array<string, mixed>  $extra
     */
    public function plus(string|Expression $column, int|float $amount = 1, array $extra = []): int|false
    {
        return static::withoutTimestamps(fn () => $this->increment($column, $amount, $extra));
    }

    /**
     * Decrement a column without updating the model's `updated_at` timestamp.
     *
     * @param  array<string, mixed>  $extra
     */
    public function minus(string|Expression $column, int|float $amount = 1, array $extra = []): int|false
    {
        return static::withoutTimestamps(fn () => $this->decrement($column, $amount, $extra));
    }

    /**
     * Increment a column without updating timestamps or firing model events.
     *
     * @param  array<string, mixed>  $extra
     */
    public function plusQuietly(string|Expression $column, int|float $amount = 1, array $extra = []): int|false
    {
        return static::withoutEvents(
            fn () => static::withoutTimestamps(
                fn () => $this->incrementQuietly($column, $amount, $extra)
            )
        );
    }

    /**
     * Decrement a column without updating timestamps or firing model events.
     *
     * @param  array<string, mixed>  $extra
     */
    public function minusQuietly(string|Expression $column, int|float $amount = 1, array $extra = []): int|false
    {
        return static::withoutEvents(
            fn () => static::withoutTimestamps(
                fn () => $this->decrementQuietly($column, $amount, $extra)
            )
        );
    }
}
