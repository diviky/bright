<?php

namespace Karla\Console;

use Karla\Traits\Builder;
use Karla\Traits\CapsuleManager;
use Illuminate\Console\Command as BaseCommand;

class Command extends BaseCommand
{
    use CapsuleManager;
    use Builder;
}
