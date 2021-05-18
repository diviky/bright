<?php

namespace Diviky\Bright\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @SuppressWarnings(PHPMD)
 */
class Money implements CastsAttributes
{
    /**
     * Currency Code.
     *
     * @var string
     */
    protected $currency;

    /**
     * Currenncy Decimal Places.
     *
     * @var int
     */
    protected $decimals = 2;

    /**
     * @param int    $decimals
     * @param string $currency
     */
    public function __construct($decimals = 2, $currency = 'INR')
    {
        $this->currency = $currency;
        $this->decimals = $decimals;
    }

    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return $this->asDecimal(($value / \pow(10, $this->decimals)), $this->decimals);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $key
     * @param mixed                               $value
     * @param array                               $attributes
     *
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return $value * \pow(10, $this->decimals);
    }

    /**
     * Convert to decimal number.
     *
     * @param float|int $value
     * @param int       $decimals
     *
     * @return string
     */
    protected function asDecimal($value, $decimals)
    {
        return \number_format($value, $decimals, '.', '');
    }
}
