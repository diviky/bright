<?php

namespace Karla\Routing;

use Illuminate\Routing\Controller as BaseController;
use Karla\Traits\Builder;
use Karla\Traits\CapsuleManager;
use Karla\Traits\HttpTrait;
use Karla\Traits\Message;
use Karla\Traits\ViewTrait;

class Controller extends BaseController
{
    use CapsuleManager;
    use HttpTrait;
    use Message;
    use Builder;
    use ViewTrait;
}
