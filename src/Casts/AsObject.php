<?php

declare(strict_types=1);

namespace Diviky\Bright\Casts;

use Diviky\Bright\Support\Collection as SupportCollection;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AsObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(protected array $arguments) {}

            public function get($model, $key, $value, $attributes)
            {
                $collectionClass = $this->arguments[0] ?? SupportCollection::class;

                if (!isset($attributes[$key])) {
                    return new $collectionClass([]);
                }

                $data = Json::decode($attributes[$key]);

                if (!is_a($collectionClass, Collection::class, true)) {
                    throw new InvalidArgumentException('The provided class must extend [' . Collection::class . '].');
                }

                $data = Arr::wrap($data);

                return new $collectionClass($data);
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => Json::encode($value)];
            }
        };
    }

    /**
     * Specify the collection for the cast.
     *
     * @param  class-string  $class
     * @return string
     */
    public static function using($class)
    {
        return static::class . ':' . $class;
    }
}
