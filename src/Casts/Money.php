<?php

declare(strict_types=1);

namespace Diviky\Bright\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\DeviatesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @SuppressWarnings(PHPMD)
 */
class Money implements CastsAttributes, DeviatesCastableAttributes
{
    /**
     * Currency Code.
     */
    protected string $currency;

    /**
     * Currenncy Decimal Places.
     */
    protected int $decimals = 2;

    /**
     * @param  null|int  $decimals
     * @param  null|string  $currency
     */
    public function __construct($decimals = null, $currency = null)
    {
        $decimals = $decimals ?? config('bright.money.decimals', 2);
        $currency = $currency ?? config('bright.money.currency', 'INR');

        $this->currency = $currency;
        $this->decimals = intval($decimals);
    }

    public static function make($decimals = null, $currency = null)
    {
        return new self($decimals, $currency);
    }

    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    #[\Override]
    public function get($model, $key, $value, $attributes)
    {
        return $this->from($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return float|int
     */
    #[\Override]
    public function set($model, $key, $value, $attributes)
    {
        return $this->to($value);
    }

    #[\Override]
    public function increment($model, string $key, $value, array $attributes): mixed
    {
        $currentMinor = $this->rawToMinorUnits($attributes[$key] ?? 0);
        $delta = is_numeric($value) ? $this->toMinorUnits($value) : 0;

        return $this->from((string) ($currentMinor + $delta));
    }

    #[\Override]
    public function decrement($model, string $key, $value, array $attributes): mixed
    {
        $currentMinor = $this->rawToMinorUnits($attributes[$key] ?? 0);
        $delta = is_numeric($value) ? $this->toMinorUnits($value) : 0;

        return $this->from((string) ($currentMinor - $delta));
    }

    /**
     * Convert a decimal (major-unit) amount to integer minor units for storage / SQL deltas.
     */
    protected function toMinorUnits(int|float|string $value): int
    {
        return (int) round((float) $value * 10 ** $this->decimals);
    }

    protected function rawToMinorUnits(mixed $raw): int
    {
        if (!is_numeric($raw)) {
            return 0;
        }

        return (int) round((float) $raw);
    }

    /**
     * Convert to int from decimals.
     *
     * @param  float|int|string  $value
     * @return float|int|string
     */
    public function to($value)
    {
        if (is_numeric($value)) {
            return $this->toMinorUnits($value);
        }

        return $value;
    }

    /**
     * Convert to int from decimals.
     *
     * @param  int|string  $value
     * @return int|string
     */
    public function from($value)
    {
        if (is_numeric($value)) {
            return $this->asDecimal($value / 10 ** $this->decimals, $this->decimals);
        }

        return $value;
    }

    /**
     * Convert to decimal number.
     *
     * @param  float|int  $value
     * @param  int  $decimals
     * @return string
     */
    protected function asDecimal($value, $decimals)
    {
        return \number_format($value, $decimals, '.', '');
    }
}
