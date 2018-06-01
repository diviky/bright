<?php

namespace Karla\Routing;

use Karla\Traits\Builder;
use Karla\Traits\Message;
use Karla\Traits\HttpTrait;
use Karla\Traits\ViewTrait;
use Karla\Traits\CapsuleManager;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use CapsuleManager, HttpTrait, Message, Builder, ViewTrait;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
