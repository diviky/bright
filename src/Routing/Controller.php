<?php

namespace Karla\Routing;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Karla\Traits\Builder;
use Karla\Traits\CapsuleManager;
use Karla\Traits\HttpTrait;
use Karla\Traits\Message;
use Karla\Traits\ViewTrait;

class Controller extends BaseController
{
    use CapsuleManager, HttpTrait, Message, Builder, ViewTrait;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
