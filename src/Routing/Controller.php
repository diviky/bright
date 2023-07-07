<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Diviky\Bright\Concerns\Builder;
use Diviky\Bright\Concerns\CapsuleManager;
use Diviky\Bright\Concerns\HttpTrait;
use Diviky\Bright\Concerns\Message;
use Diviky\Bright\Concerns\ViewTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use CapsuleManager;
    use HttpTrait;
    use Message;
    use Builder;
    use ViewTrait;
    use AuthorizesRequests;
    use ValidatesRequests;
}
