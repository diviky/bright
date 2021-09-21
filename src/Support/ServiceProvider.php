<?php

declare(strict_types=1);

namespace Diviky\Bright\Support;

use Diviky\Bright\Concerns\ServiceProviderExtra;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    use ServiceProviderExtra;
}
