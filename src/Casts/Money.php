<?php

namespace Diviky\Bright\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Money implements CastsAttributes
{
    protected $currency;

    protected $decimals = 2;

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

    protected function asDecimal($value, $decimals)
    {
        return \number_format($value, $decimals, '.', '');
    }
}
