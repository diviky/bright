<?php

namespace Karla\Http\Controllers;

use Karla\Traits\Builder;
use Karla\Traits\CapsuleManager;
use Karla\Traits\HttpTrait;
use Karla\Traits\Message;
use Karla\Traits\ViewTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests,
    DispatchesJobs,
    ValidatesRequests,
    CapsuleManager,
    HttpTrait,
    Message,
    Builder,
        ViewTrait;
}
