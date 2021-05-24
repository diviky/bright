<?php

declare(strict_types=1);

namespace Diviky\Bright\Console;

use Diviky\Bright\Traits\CapsuleManager;
use Illuminate\Console\Command as BaseCommand;

class Command extends BaseCommand
{
    use CapsuleManager;
}
