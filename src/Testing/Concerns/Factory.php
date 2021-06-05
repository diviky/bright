<?php

declare(strict_types=1);

namespace Diviky\Bright\Testing\Concerns;

use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Support\Str;

trait Factory
{
    /**
     * Register factories.
     *
     * @param string $namespace
     */
    protected function registerFactoriesFor($namespace): void
    {
        EloquentFactory::guessFactoryNamesUsing(function (string $modelName) use ($namespace) {
            if (Str::startsWith($modelName, $namespace . '\\')) {
                return $namespace . '\\Database\\Factories\\' . class_basename($modelName) . 'Factory';
            }

            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }
}
