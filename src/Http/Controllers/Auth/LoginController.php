<?php

namespace Karla\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Karla\Routing\Controller;

class LoginController extends Controller
{
    protected $maxAttempts  = 5;
    protected $decayMinutes = 10;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected function username()
    {
        return 'username';
    }
}
