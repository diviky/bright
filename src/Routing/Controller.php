<?php

namespace Diviky\Bright\Routing;

use Diviky\Bright\Traits\Builder;
use Diviky\Bright\Traits\CapsuleManager;
use Diviky\Bright\Traits\HttpTrait;
use Diviky\Bright\Traits\Message;
use Diviky\Bright\Traits\ViewTrait;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use CapsuleManager;
    use HttpTrait;
    use Message;
    use Builder;
    use ViewTrait;
}
