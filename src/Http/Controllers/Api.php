<?php

namespace Karla\Http\Controllers;

use Karla\Traits\CapsuleManager;
use Karla\Traits\HttpTrait;
use Karla\Traits\ViewTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Api extends BaseController
{
    use AuthorizesRequests,
    DispatchesJobs,
    ValidatesRequests,
    CapsuleManager,
    HttpTrait,
        ViewTrait;
}
